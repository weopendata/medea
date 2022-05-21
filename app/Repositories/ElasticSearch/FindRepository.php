<?php

namespace App\Repositories\ElasticSearch;

use App\Repositories\Eloquent\PanTypologyRepository;
use Carbon\Carbon;
use Elastica\Aggregation\GeohashGrid;
use Elastica\Aggregation\GeotileGridAggregation;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\MatchQuery;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Elastica\Query\Wildcard;
use Elastica\ResultSet;

class FindRepository extends BaseRepository
{
    /**
     * @const array
     */
    const AGGREGATION_FIELDS = [
        'objectMaterial',
        'collection',
        'objectCategory',
        'objectPeriod',
        'modification',
        'collection',
        'mark',
        'inscription',
        'complete',
        'findSpotLocality',
        'excavationLocality',
        'photographCaptionPresent',
    ];

    /**
     * @const array
     */
    const PRESENCE_FACET_PROPERTIES = [
        'mark',
        'insignia',
        'complete',
        'photographCaptionPresent',
    ];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $indexName = env('ELASTIC_FINDS_INDEX_NAME');

        if (empty($indexName)) {
            throw new \Exception('No ElasticSearch index specified for "finds".');
        }

        parent::__construct($indexName);
    }

    /**
     * @param  array $find
     * @return bool|string
     */
    public function store(array $find)
    {
        $findDocument = $this->createDocument($find, array_get($find, 'elasticSearchId'));

        try {
            $response = $this->index->addDocument($findDocument);

            if ($response->isOk()) {
                // Store the changes
                return $response->getData()['_id'];
            }

            \Log::error($response->getError());
        } catch (ResponseException $ex) {
            \Log::error($findDocument->getData());

            $error = $ex->getResponse()->getFullError();

            if (is_array($error)) {

                $error = array_get($error, 'root_cause.0.reason');
            }

            \Log::error('Could not add the document to elastic, the error we got is: ' . $error);
        }

        return false;
    }

    /**
     * Update a document and return its ID
     *
     * @param  string $elasticId
     * @param  array  $find
     * @return boolean
     */
    public function update(string $elasticId, array $find)
    {
        $findDocument = $this->createDocument($find, $elasticId);

        try {
            $response = $this->index->updateDocument($findDocument, ['refresh' => true, 'retry_on_conflict' => 1]);

            if ($response->isOk()) {
                return true;
            }

            \Log::error($response->getError());
        } catch (ResponseException $ex) {
            $error = $ex->getResponse()->getFullError();

            if (is_array($error)) {
                $error = array_get($error, 'root_cause.0.reason');
            }

            \Log::error('Could not add the document to elastic, the error we got is: ' . $error, $find);
        }

        return false;
    }

    /**
     * @param  int $neo4jId
     * @return array
     */
    public function getByNeo4jId(int $neo4jId)
    {
        $term = new Term();
        $term->setParam('findId', $neo4jId);

        $query = new Query();
        $query->setQuery($term);

        $search = $this->createSearch();
        $search->setQuery($query);

        $results = $this->performSearch($search);

        if (empty($results)) {
            return [];
        }

        return $results[0];
    }

    /**
     * @param  array       $filters
     * @param  int|null    $limit
     * @param  int|null    $offset
     * @param  string|null $orderBy
     * @param  string|null $orderFlow
     * @return array
     */
    public function getAllWithFilter(
        array   $filters,
        ?int    $limit = 20,
        ?int    $offset = 0,
        ?string $orderBy = 'findDate',
        ?string $orderFlow = 'ASC'
    ): array
    {
        $boolQuery = $this->buildQueryFromFilters($filters);

        $orderBy = @$this->getFilterKeyToPropertyMapping()[$orderBy] ?? $orderBy;

        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setSize($limit);
        $query->setFrom($offset);
        $query->addSort([$orderBy => ['order' => $orderFlow]]);

        $this->applyFacetAggregations($query);

        $search = $this->createSearch();
        $search->setQuery($query);

        $resultSet = $search->search();

        $findResults = $this->parseDocumentsFromResultSet($resultSet);
        $findResults = $this->appendMetaDataToFindResults($findResults);
        $facetCounts = $this->parseAggregations($resultSet, $filters);

        return [
            'data' => $findResults,
            'facets' => $facetCounts,
            'total' => $resultSet->getTotalHits(),
        ];
    }

    /**
     * @param  array|null $filters
     * @return array
     */
    public function getFindLocations(?array $filters = []): array
    {
        $boolQuery = $this->buildQueryFromFilters($filters);
        $boolQuery->addFilter(new Exists('location'));

        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setSize(1000);
        $query->setSource(['location', 'findId']);

        $search = $this->createSearch();
        $search->setQuery($query);

        return [
            'markers' => $this->performSearch($search),
            'total' => $this->getTotalHits(),
        ];
    }

    /**
     * @param  array|null $filters
     * @return array
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geohashgrid-aggregation.html
     */
    public function getHeatMap(?array $filters = []): array
    {
        $boolQuery = $this->buildQueryFromFilters($filters);
        $boolQuery->addFilter(new Exists('location'));

        $geoGridAggregation = new GeohashGrid('geoGridAggregation', 'location');
        $geoGridAggregation->setPrecision(5);

        $query = new Query();
        $query->setQuery($boolQuery);
        $query->addAggregation($geoGridAggregation);

        $search = $this->createSearch();
        $search->setQuery($query);

        $resultSet = $search->search();
        $geoGridAggregationResults = $resultSet->getAggregation('geoGridAggregation');

        return array_get($geoGridAggregationResults, 'buckets');
    }

    /**
     * @param  array $filters
     * @return BoolQuery
     */
    private function buildQueryFromFilters(array $filters): BoolQuery
    {
        $boolQuery = new BoolQuery();

        $filters = $this->keepValidFilters($filters);

        foreach ($filters as $filterName => $filterValue) {
            $this->applyFilter($boolQuery, $filterName, $filterValue);
        }

        return $boolQuery;
    }

    /**
     * @param  Query $query
     * @return void
     */
    private function applyFacetAggregations(Query $query)
    {
        foreach (self::AGGREGATION_FIELDS as $aggregationField) {
            $query->addAggregation($this->createAggregationQuery($aggregationField));
        }
    }

    /**
     * @param  string $field
     * @param  int    $size
     * @return \Elastica\Aggregation\Terms
     */
    private function createAggregationQuery(string $field, int $size = 50)
    {
        $aggregation = new \Elastica\Aggregation\Terms($field);
        $aggregation->setField($field);
        $aggregation->setSize($size);

        return $aggregation;
    }


    /**
     * @param  BoolQuery $query
     * @param  string    $filterName
     * @param  mixed     $filterValue
     * @return void
     */
    private function applyFilter(BoolQuery $query, string $filterName, $filterValue)
    {
        if (empty($filterValue) || !is_string($filterValue)) {
            return;
        }

        $propertyName = $this->getFilterKeyToPropertyMapping()[$filterName];

        if (in_array($propertyName, self::PRESENCE_FACET_PROPERTIES)) {
            // unless the value is empty, there's only 1 way we interpret this filter, which is either
            // the absence of a value for the field
            // OR
            // a non "nee" value
            $existsQuery = new Exists($propertyName);

            $termQuery = new Term();
            $termQuery->setParam($propertyName, 'nee');

            $boolQuery = new BoolQuery();
            $boolQuery->addShould((new BoolQuery())->addMustNot($existsQuery));
            $boolQuery->addShould((new BoolQuery())->addMustNot($termQuery));
            $boolQuery->setMinimumShouldMatch(1);

            $query->addFilter($boolQuery);

            return;
        }

        if (in_array($propertyName, ['panFinalPeriod'])) {
            $query
                ->addFilter(
                    new Range($propertyName, [
                        'lte' => (int)$filterValue,
                    ])
                );

            return;
        }

        if (in_array($propertyName, ['panInitialPeriod'])) {
            $query
                ->addFilter(
                    new Range($propertyName, [
                        'gte' => (int)$filterValue,
                    ])
                );

            return;
        }

        if ($propertyName === 'fts_description') {
            $match = new MatchQuery();
            $match->setFieldParam($propertyName, 'query', $filterValue);
            $match->setFieldFuzziness($propertyName, 3);

            $query->addMust($match);

            return;
        }

        if ($propertyName == 'panId') {
            $wildCard = new Wildcard($propertyName, $filterValue . '*');

            $query->addMust($wildCard);

            return;
        }

        $termQuery = new Term();
        $termQuery->setParam($propertyName, $filterValue);

        $query->addFilter($termQuery);
    }

    /**
     * @param  array $filters
     * @return array
     */
    private function keepValidFilters(array $filters): array
    {
        return array_only($filters, array_keys($this->getFilterKeyToPropertyMapping()));
    }

    /**
     * @return array
     */
    private function getFilterKeyToPropertyMapping(): array
    {
        return [
            'validation' => 'validation',
            'photographCaption' => 'photographCaptionPresent',
            'category' => 'objectCategory',
            'period' => 'objectPeriod',
            'objectMaterial' => 'objectMaterial',
            'findSpotLocation' => 'findSpotLocality',
            'excavationLocation' => 'excavationLocality',
            'modification' => 'modification',
            'volledigheid' => 'complete',
            'merkteken' => 'mark',
            'opschrift' => 'inscription',
            'collection' => 'collection',
            'identifier' => 'findId',
            'startYear' => 'panInitialPeriod',
            'endYear' => 'panFinalPeriod',
            'embargo' => 'embargo',
            'query' => 'fts_description',
            'panid' => 'panId',
            'finderEmail' => 'finderEmail',
        ];
    }

    /**
     * @param  array       $find
     * @param  string|null $elasticSearchId
     * @return Document
     */
    private function createDocument(array $find, ?string $elasticSearchId = null): Document
    {
        $findDate = null;

        if (!empty($find['findDate']) && $findDate['findDate'] !== 'onbekend') {
            try {
                $findDate = new Carbon($findDate['findDate']);
                $findDate = $findDate->toDateString();
            } catch (\Exception $ex) {
                //
            }
        }

        $location = [
            'lat' => null,
            'lon' => null,
        ];

        if (!empty($find['lng']) && !empty($find['lat']) && is_double($find['lng']) && is_double($find['lat'])) {
            $location['lat'] = $find['lat'];
            $location['lon'] = $find['lng'];
        }

        if (!empty($find['excavationLng']) && !empty($find['excavationLat']) && is_double($find['excavationLng']) && is_double($find['excavationLat'])) {
            $location['lat'] = $find['excavationLat'];
            $location['lon'] = $find['excavationLng'];
        }

        $ftsDescription = '';

        $ftsFields = [
            'category',
            'objectNr',
            'identifier',
            'objectDescription',
            'material',
            'modification',
            'technique',
            'identifier',
        ];

        foreach ($ftsFields as $ftsField) {
            $ftsDescription .= ' ' . @$find[$ftsField];
        }

        $findDocument = [
            'findId' => $find['identifier'],
            'excavationTitle' => @$find['excavationTitle'],
            'findDate' => $findDate,
            'objectNr' => array_get($find, 'objectNr'),
            'objectCategory' => array_get($find, 'category'),
            'objectPeriod' => array_get($find, 'period'),
            'objectMaterial' => array_get($find, 'material'),
            'validation' => array_get($find, 'validation'),
            'objectTechnique' => array_get($find, 'technique'),
            'modification' => array_get($find, 'modification'),
            'findSpotLocality' => array_get($find, 'locality'),
            'excavationLocality' => array_get($find, 'excavationAddressLocality'),
            'accuracy' => array_get($find, 'accuracy'),
            'finderId' => array_get($find, 'finderId'),
            'finderEmail' => array_get($find, 'email'),
            'panId' => array_get($find, 'panId'),
            'panInitialPeriod' => array_get($find, 'panTypologyInfo.initialPeriod'),
            'panFinalPeriod' => array_get($find, 'panTypologyInfo.finalPeriod'),
            'complete' => in_array(strtolower(array_get($find, 'complete') ?? ''), ["nee", "neen", "onbekend"]) ? 'Nee' : 'Ja',
            'mark' => in_array(strtolower(array_get($find, 'mark') ?? ''), ["nee", "neen", "onbekend"]) ? 'Nee' : 'Ja',
            'inscription' => in_array(strtolower(array_get($find, 'insignia') ?? ''), ["nee", "neen", "onbekend"]) ? 'Nee' : 'Ja',
            'photograph_path' => array_get($find, 'photograph'),
            'photographCaptionPresent' => !empty($find['photographCaption']) ? 'yes' : 'no',
            'embargo' => array_get($find, 'embargo'),
            'collection' => array_get($find, 'collection'),
            'fts_description' => trim($ftsDescription),
        ];

        if (!empty($location['lat'])) {
            $findDocument['location'] = $location;
        }

        return new Document($elasticSearchId, $findDocument);
    }

    /**
     * @param  ResultSet $resultSet
     * @return array
     */
    private function parseDocumentsFromResultSet(ResultSet $resultSet): array
    {
        $findResults = [];

        foreach ($resultSet->getResults() as $result) {
            $data = $result->getData();

            if (!empty($fields)) {
                $data = array_only($data, $fields);
            }

            $data['id'] = $result->getId();

            $findResults[] = $data;
        }

        return $findResults;
    }

    /**
     * @param  ResultSet $resultSet
     * @return array
     */
    private function parseAggregations(ResultSet $resultSet, array $filters): array
    {
        $facetCounts = [];
        $filterNameToPropertyNameMapping = array_flip($this->getFilterKeyToPropertyMapping());

        foreach (self::AGGREGATION_FIELDS as $aggregationField) {
            $facetCount = array_get($resultSet->getAggregation($aggregationField), 'buckets') ?? [];
            $facetCount = $this->transformAggregationBucket($facetCount);

            $filterNameForAggregationField = $filterNameToPropertyNameMapping[$aggregationField];

            // Append the filter value to the corresponding facet count, if the filter value is not in the facet count
            if (!empty($filters[$filterNameForAggregationField]) && !array_key_exists($filters[$filterNameForAggregationField], $facetCount)) {
                $facetCount[$filters[$filterNameForAggregationField]] = 0;
            }

            $facetCounts[$aggregationField] = $facetCount;
        }

        return $facetCounts;
    }

    /**
     * @param  array $findResults
     * @return array
     */
    private function appendMetaDataToFindResults(array $findResults)
    {
        $panIds = collect(array_pluck($findResults, 'panId') ?? [])
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $panTypologyInformation = app(PanTypologyRepository::class)->getPanTypologyInformationForIds($panIds);

        // Append PAN reference type information if applicable
        return collect($findResults)
            ->map(function ($result) use ($panTypologyInformation) {
                $result['_panTypologyInfo'] = @$panTypologyInformation[$result['panId']] ?? [];

                return $result;
            })
            ->toArray();
    }
}
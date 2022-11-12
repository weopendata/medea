<?php

namespace App\Repositories\ElasticSearch;

use App\Repositories\Eloquent\PanTypologyRepository;
use Carbon\Carbon;
use Elastica\Aggregation\GeohashGrid;
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
        'excavationTitle'
    ];

    /**
     * List of properties that have a "yes"/"no" value, they're either present or they are not
     *
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
     * @param  array|null  $filters
     * @param  int|null    $limit
     * @param  int|null    $offset
     * @param  string|null $orderBy
     * @param  string|null $orderFlow
     * @param  bool        $withFacets
     * @return array
     */
    public function getAllWithFilter(
        ?array  $filters = [],
        ?int    $limit = 20,
        ?int    $offset = 0,
        ?string $orderBy = 'findDate',
        ?string $orderFlow = 'ASC',
        bool $withFacets = true
    ): array
    {
        $query = $this->createSearchQuery($filters, $orderBy, $orderFlow, $limit, $offset);

        if ($withFacets) {
            $this->applyFacetAggregations($query);
        }

        $search = $this->createSearch();
        $search->setQuery($query);

        $resultSet = $search->search();

        $findResults = $this->parseDocumentsFromResultSet($resultSet);
        $findResults = $this->appendMetaDataToFindResults($findResults);

        $facetCounts = [];

        if ($withFacets) {
            $facetCounts = $this->parseAggregations($resultSet, $filters);
        }

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
            $matchPhrase = new Query\MatchPhrase();
            //$matchPhrase->setParam($propertyName, '\"' . $filterValue . '\"');
            $matchPhrase->setParam($propertyName, $filterValue);

            $query->addMust($matchPhrase);

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
            'excavationTitle' => 'excavationTitle',
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

        if (!empty($find['findDate']) && $find['findDate'] !== 'onbekend') {
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

        if (!empty($find['lng']) && !empty($find['lat']) && is_numeric($find['lng']) && is_numeric($find['lat'])) {
            $location['lat'] = (double)$find['lat'];
            $location['lon'] = (double)$find['lng'];
        }

        if (!empty($find['excavationLng']) && !empty($find['excavationLat']) && is_numeric($find['excavationLng']) && is_numeric($find['excavationLat'])) {
            $location['lat'] = (double)$find['excavationLat'];
            $location['lon'] = (double)$find['excavationLng'];
        }

        $ftsDescription = '';

        $ftsFields = [
            'category',
            'objectNr',
            'objectDescription',
            'material',
            'modification',
            'technique',
            'identifier',
            'excavationTitle',
            'excavationId',
            'contextLegacyId',
            'findUUID',
            'classificationDescription',
            'objectDescription',
            'findUUID',
            'findId',
            'panLabel',
            'panClassificationDescription',
        ];

        foreach ($ftsFields as $ftsField) {
            $ftsFieldValue = @$find[$ftsField];

            if (empty($ftsFieldValue) || is_array($ftsFieldValue)) {
                continue;
            }

            $ftsDescription .= ' ' . $ftsFieldValue;
        }

        // Convert fields that are indexed with numerical doubles to actual double typed values
        $fieldsThatAreDoubles = [
            'length',
            'width',
            'height',
            'diameter',
            'weight',
            'amount',
        ];

        foreach ($fieldsThatAreDoubles as $doubleField) {
            if (empty($find[$doubleField])) {
                $find[$doubleField] = null;

                continue;
            }

            $find[$doubleField] = (double)$find[$doubleField];
        }

        $findDocument = [
            'findId' => $find['findId'],
            'findUUID' => @$find['findUUID'],
            'excavationId' => @$find['excavationId'],
            'contextId' => @$find['contextId'],
            'excavationTitle' => @$find['excavationTitle'],
            'findDate' => $findDate,
            'objectNr' => array_get($find, 'objectNr'),
            'objectCategory' => array_get($find, 'objectCategory'),
            'objectPeriod' => array_get($find, 'objectPeriod'),
            'objectMaterial' => array_get($find, 'objectMaterial'),
            'objectDescription' => array_get($find, 'objectDescription'),
            'objectTechnique' => array_get($find, 'objectTechnique'),
            'validation' => array_get($find, 'validation'),
            'modification' => array_get($find, 'modification'),
            'surfaceTreatment' => array_get($find, 'treatment'),
            'width' => array_get($find, 'width'),
            'widthUnit' => array_get($find, 'widthUnit'),
            'height' => array_get($find, 'height'),
            'heightUnit' => array_get($find, 'heightUnit'),
            'length' => array_get($find, 'length'),
            'lengthUnit' => array_get($find, 'lengthUnit'),
            'diameter' => array_get($find, 'diameter'),
            'diameterUnit' => array_get($find, 'diameterUnit'),
            'weight' => array_get($find, 'weight'),
            'weightUnit' => array_get($find, 'weightUnit'),
            'amount' => array_get($find, 'amount'),
            'findSpotLocality' => array_get($find, 'findSpotLocality'),
            'excavationLocality' => array_get($find, 'excavationLocality'),
            'accuracy' => array_get($find, 'accuracy'),
            'finderId' => array_get($find, 'finderId'),
            'finderEmail' => array_get($find, 'finderEmail'),
            'panId' => array_get($find, 'panId'),
            'panInitialPeriod' => array_get($find, 'panInitialPeriod'),
            'panFinalPeriod' => array_get($find, 'panFinalPeriod'),
            'panLabel' => array_get($find, 'panLabel'),
            'panClassificationDescription' => array_get($find, 'panClassificationDescription'),
            'classificationDescription' => array_get($find, 'classificationDescription'),
            'conservation' => array_get($find, 'conservation'),
            'complete' => array_get($find, 'complete'),
            'mark' => array_get($find, 'mark'),
            'inscription' => array_get($find, 'inscription'),
            'photographPath' => array_get($find, 'photographPath'),
            'photographCaption' => array_get($find, 'photographCaption'),
            'photographNote' => array_get($find, 'photographNote'),
            'photographAttribution' => array_get($find, 'photographAttribution'),
            'photographLicense' => array_get($find, 'photographLicense'),
            'photographCaptionPresent' => array_get($find, 'photographCaptionPresent'),
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
    private function appendMetaDataToFindResults(array $findResults): array
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

    /**
     * @param  array  $filters
     * @param  string $orderBy
     * @param  string $orderFlow
     * @param  int    $limit
     * @param  int    $offset
     * @return Query
     */
    private function createSearchQuery(array $filters, string $orderBy, string $orderFlow, int $limit, int $offset): Query
    {
        $boolQuery = $this->buildQueryFromFilters($filters);

        $orderBy = @$this->getFilterKeyToPropertyMapping()[$orderBy] ?? $orderBy;

        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setSize($limit);
        $query->setFrom($offset);
        $query->addSort([$orderBy => ['order' => $orderFlow]]);

        return $query;
    }
}
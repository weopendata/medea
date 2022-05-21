<?php

namespace App\Repositories\ElasticSearch;

use App\Repositories\Eloquent\PanTypologyRepository;
use Carbon\Carbon;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
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
        'photographCaptionPresent'
        // TODO: add the rest of the facets
        /**
         * 'category' => 'collect(distinct [p in (object:E22)-[:P2]-(:E55{name:"objectCategory"}) | last(nodes(p)).value])',
         * 'period' => 'collect(distinct [p in (object:E22)-[:P42]-(:E55{name:"period"}) | last(nodes(p)).value])',
         * 'objectMaterial' => 'collect(distinct [p in (object:E22)-[:P45]-(:E57) | last(nodes(p)).value])',
         * 'modification' => 'collect(distinct [p in (object:E22)-[:P108]->(:E11)-[:P33]->(:E29)-[:P2]->(:E55) | last(nodes(p)).value])',
         * 'collection' => 'collect(distinct [p in (object:E22)-[:P24]-(:E78) | id(last(nodes(p)))])',
         * 'featureTypes' => 'collect(distinct [p in (object:E22)-[:P56]->(:E25)-[:P2]->(:E55) | last(nodes(p)).value])',
         * 'findSpotLocation' => 'collect(distinct [p in (find:E10)-[:P7]->(:E27)-[:P53]->(:E53)-[:P89]->(:E53)-[:P87]->(:E45{name:"locationAddressLocality"}) | last(nodes(p)).value])',
         * 'excavationLocation' => 'collect(distinct [p in (excavationEvent:A9)-[:AP3]->(:E27)-[:P53]->(:E53)-[:P89]->(:E53)-[:P87]->(:E45{name:"locationAddressLocality"}) | last(nodes(p)).value])',
         */
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
        $boolQuery = new BoolQuery();

        $filters = $this->keepValidFilters($filters);

        foreach ($filters as $filterName => $filterValue) {
            $this->applyFilter($boolQuery, $filterName, $filterValue);
        }

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
        $facetCounts = $this->parseAggregations($resultSet);

        return [
            'data' => $findResults,
            'facets' => $facetCounts,
            'total' => $resultSet->getTotalHits(),
        ];

        /**
         *  $matchStatements = [];
         * $whereStatements = [];
         *
         * $email = @$filters['myfinds'];
         *
         * $variables = [];
         *
         * $startStatement = '';
         *
         * if (!empty($filters['query'])) {
         * // Replace the whitespace with the Lucene syntax for white spaces text queries
         * $query = preg_replace('#\s+#', ' AND ', $filters['query']);
         *
         * $startStatement = "START object=node:node_auto_index('fulltext_description:(*" . $query . "*)') ";
         * }
         *
         * // Non-personal find statement
         * $initialStatement = '(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation), (find:E10)-[P4]-(findDate:E52)';
         *
         * $withStatement = ['validation', 'person'];
         *
         * // In our query find.id is aliased as identifier
         * $orderStatement = 'identifier ' . $orderFlow;
         *
         * if ($orderBy == 'period') {
         * $matchStatements[] = '(object:E22)-[P42]-(period:E55)';
         * $withStatement[] = 'period';
         * $orderStatement = "period.value $orderFlow";
         * } else if ($orderBy == 'findDate') {
         * $withStatement[] = 'findDate';
         * $orderStatement = "findDate.value $orderFlow";
         * }
         *
         * foreach ($this->getFilterPropertyQueryStatements() as $property => $config) {
         * if (isset($filters[$property])) {
         * $matchStatements[] = $config['match'];
         *
         * if (!empty($config['where'])) {
         * $whereStatements[] = $config['where'];
         * }
         *
         * $variables[$config['whereVariableName']] = $filters[$property];
         *
         * // If we have an integer value, convert the value we received from the request URI
         * // Neo4j makes a strict distinction between integers and strings
         * if (@$config['varType'] == 'int') {
         * $variables[$config['whereVariableName']] = (int)$filters[$property];
         * }
         *
         * if (!empty($config['with']) && !in_array($config['with'], $this->getDefaultWithStatementProperties())) {
         * $withStatement[] = $config['with'];
         * }
         * }
         * }
         *
         * if (!empty($email)) {
         * if ($validationStatus == '*') {
         * $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
         * } else {
         * $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
         * $variables['validationStatus'] = $validationStatus;
         * }
         * }
         *
         * // Add the multi-tenancy statement
         * $whereStatements[] = NodeService::getTenantWhereStatement(['object', 'find']);
         *
         * $matchStatement = implode(', ', $matchStatements);
         * $whereStatement = implode(' AND ', $whereStatements);
         *
         * // Add the optional statements
         * $availableOptionalStatements = [
         * 'person' => [
         * 'match' => '(find:E10)-[P29]-(person:person)',
         * 'where' => NodeService::getTenantWhereStatement(['find', 'person']),
         * ],
         * 'typology' => [
         * 'match' => '(object:E22)-[r:P108]->(productionEvent:productionEvent)-[:P41]->(productionClassification:productionClassification)-[:P42]->(typologyClassification:E55), (productionClassification:productionClassification)-[:P2]-(pcvType:E55 {value: "Typologie"})',
         * 'where' => NodeService::getTenantWhereStatement(['object', 'productionEvent', 'productionClassification', 'typologyClassification']),
         * 'with' => ['typologyClassification'],
         * ],
         * 'excavationTitle' => [
         * 'match' => '(excavationEvent:A9)-[:P1]->(excavationTitle:E41)',
         * 'where' => NodeService::getTenantWhereStatement(['excavationEvent']) . ' AND excavationEvent.internalId = find.excavationId',
         * 'with' => ['excavationTitle'],
         * ],
         * 'excavationLocation' => [
         * 'match' => '(excavationEvent:A9)-[:AP3]->(:E27)-[:P53]->(:E53)-[:P89]->(:E53)-[:P87]->(excavationLocation:E45{name:"locationAddressLocality"})',
         * 'where' => NodeService::getTenantWhereStatement(['excavationEvent']) . ' AND excavationEvent.internalId = find.excavationId',
         * 'with' => ['excavationLocation'],
         * ],
         * 'volledigheid' => [
         * 'match' => '(object:E22)-[:P56]->(complete:E25)-[:P2]->(:E55 {value: "volledigheid"})',
         * 'where' => NodeService::getTenantWhereStatement(['complete']),
         * ],
         * 'merkteken' => [
         * 'match' => '(object:E22)-[:P56]->(mark:E25)-[:P2]->(:E55 {value: "merkteken"})',
         * 'where' => NodeService::getTenantWhereStatement(['mark']),
         * ],
         * 'opschrift' => [
         * 'match' => '(object:E22)-[:P56]->(insignia:E25)-[:P2]->(:E55 {value: "opschrift"})',
         * 'where' => NodeService::getTenantWhereStatement(['insignia']),
         * ],
         * ];
         *
         * $optionalStatements = [];
         *
         * foreach ($availableOptionalStatements as $optionalStatementName => $availableOptionalStatement) {
         * if (in_array($optionalStatementName, $excludeOptionalStatements)) {
         * continue;
         * }
         *
         * $optionalStatements[] = $availableOptionalStatement;
         *
         * if (!empty($availableOptionalStatement['with'])) {
         * $withStatement = array_merge($withStatement, $availableOptionalStatement['with']);
         * }
         * }
         */
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
        if (is_string($filterValue)) {
            $termQuery = new Term();
            $termQuery->setParam($filterName, $filterValue);

            $query->addFilter($termQuery);
        }
    }

    /**
     * @param  array $filters
     * @return array
     */
    private function keepValidFilters(array $filters): array
    {
        return array_only($filters, $this->getFilterKeyNames());
    }

    /**
     * @return array
     */
    private function getFilterKeyNames(): array
    {
        return [
            'validation',
        ];
    }

    /**
     * @param  array       $find
     * @param  string|null $elasticSearchId
     * @return Document
     */
    private function createDocument(array $find, ?string $elasticSearchId = null)
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

        if (!empty($find['lng']) && !empty($find['lat'])) {
            $location['lat'] = $find['lat'];
            $location['lon'] = $find['lng'];
        }

        if (!empty($find['excavationLng']) && !empty($find['excavationLat'])) {
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
     * @return void
     */
    private function parseAggregations(ResultSet $resultSet): array
    {
        $facetCounts = [];

        foreach (self::AGGREGATION_FIELDS as $aggregationField) {
            $facetCount = array_get($resultSet->getAggregation($aggregationField), 'buckets') ?? [];
            $facetCounts[$aggregationField] = $this->transformAggregationBucket($facetCount);
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
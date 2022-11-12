<?php

namespace App\Repositories;

use App\Models\FindEvent;
use App\Repositories\Eloquent\PanTypologyRepository;
use App\Services\NodeService;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Query\ResultSet;

/**
 * Class FindRepository
 *
 * @package App\Repositories
 */
class FindRepository extends BaseRepository
{
    /**
     * FindRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(FindEvent::$NODE_TYPE, FindEvent::class);
    }

    /**
     * @param  array $properties
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function store(array $properties)
    {
        $find = new FindEvent($properties);
        $find->save();

        $findId = $find->getId();

        return $findId;
    }

    /**
     * Return meta data for a find
     *
     * - excavation
     * - typology
     *
     * @param  integer $findId
     * @return array
     * @throws \Everyman\Neo4j\Exception
     */
    public function getMeta($findId)
    {
        $node = $this->getById($findId);

        $excavationUUID = $node->getProperty('excavationId');

        return app(ExcavationRepository::class)->getBaseInformationForExcavation($excavationUUID);
    }

    /**
     * @param  int  $findId
     * @param  null $user
     * @return array
     * @throws \Everyman\Neo4j\Exception
     */
    public function expandValues($findId, $user = null): array
    {
        $find = parent::expandValues($findId);

        if (empty($user)) {
            return $find;
        }

        // Add the vote of the user
        // Get the vote of the user for the find
        $tenantStatement = NodeService::getTenantWhereStatement(['person', 'classification', 'find']);
        $whereStatement = 'WHERE id(person) = {userId} AND id(find) = {findId} AND ' . $tenantStatement;;

        // Make the query
        $query = 'MATCH (person:person)-[r]-(classification:productionClassification)-[*2..3]-(find:E10) ' . $whereStatement . '  RETURN r';

        $variables = [];
        $variables['userId'] = (int)$user->id;
        $variables['findId'] = (int)$findId; // Cast it to an integer typed value, Neo4J does not convert it automatically

        $client = $this->getClient();

        $cypher_query = new Query($client, $query, $variables);
        $results = $cypher_query->getResultSet();

        if ($results->count() == 0) {
            return $find;
        }

        foreach ($results as $result) {
            $relationship = $result->current();

            $classification_id = $relationship->getEndNode()->getId();

            if (!empty($find['object']) && !empty($find['object']['productionEvent']['productionClassification'])) {
                foreach ($find['object']['productionEvent']['productionClassification'] as $index => $classification) {
                    if ($classification['identifier'] == $classification_id) {
                        $find['object']['productionEvent']['productionClassification'][$index]['me'] = $relationship->getType();
                    }
                }
            }
        }

        return $find;
    }

    /**
     * Return FindEvents that are filtered
     * We expect that all filters are object filters (e.g. category, period, technique, material)
     * We'll have to build our query based on the filters that are configured,
     * some are filters on object relationships, some on find events, some on classifications
     *
     * @param  array  $filters
     * @param  int    $limit
     * @param  int    $offset
     * @param  string $orderBy
     * @param  string $orderFlow
     * @param  string $validationStatus
     * @return array
     * @throws \Exception
     */
    public function getAllWithFilter(
        $filters,
        $limit = 20,
        $offset = 0,
        $orderBy = 'findDate',
        $orderFlow = 'ASC',
        $validationStatus = '*'
    ) {
        extract(
            $this->prepareFilteredFindsListQuery($filters, $validationStatus, $limit, $offset, $orderBy, $orderFlow)
        );

        $cypherQuery = new Query($this->getClient(), $query, $variables);

        $data = $this->parseFilteredFindsListResults($cypherQuery->getResultSet());

        return ['data' => $data];
    }

    /**
     * Prepare the filtered cypher query
     *
     *
     * @param  array   $filters
     * @param  string  $validationStatus
     * @param  integer $limit
     * @param  integer $offset
     * @param  string  $orderBy
     * @param  string  $orderFlow
     * @return array
     * @throws \Exception
     */
    private function prepareFilteredFindsListQuery(
        $filters,
        $validationStatus,
        $limit = 50,
        $offset = 0,
        $orderBy = 'findDate',
        $orderFlow = 'ASC'
    ) {
        extract($this->getQueryStatements($filters, $orderBy, $orderFlow, $validationStatus));

        $withProperties = [
            'find',
            'object.embargo as embargo',
            'findDate',
            'person',
            'validation',
            '[p in (object)-[:P1]->(:E42) | last(nodes(p)).value] as objectNr',
            '[p in (object)-[:P108]-(:E12)-[:P41]-(:E17) | last(nodes(p))] as classifications',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53) | last(nodes(p))] as location',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53)-[:P87]-(:E47{name:"lat"}) | last(nodes(p))] as latitude',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53)-[:P87]-(:E47{name:"lng"}) | last(nodes(p))] as longitude',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53)-[:P89]-(:E53)-[:P87]-(:locationAddressLocality)|last(nodes(p))] as locality',
            '[p in (object:E22)-[:P45]-(:E57) | last(nodes(p))] as material',
            '[p in (object:E22)-[:P108]-(:E12)-[:P33]-(:E29)-[:P2]-(:E55) | last(nodes(p))] as technique',
            '[p in (object:E22)-[:P108]-(:E11)-[:P33]-(:E29)-[:P2]-(:E55) | last(nodes(p))] as modification',
            '[p in (object:E22)-[:P157]-(:S22)-[:P53]->(:E27)-[:P53]->(:E53)-[:P87]-(:E47{name:"lat"}) | last(nodes(p))] as excavationLatitude',
            '[p in (object:E22)-[:P157]-(:S22)-[:P53]->(:E27)-[:P53]->(:E53)-[:P87]-(:E47{name:"lng"}) | last(nodes(p))] as excavationLongitude',
            '[p in (complete:E25)-[:P3]->(:E62) | last(nodes(p))] as complete',
            '[p in (mark:E25)-[:P3]->(:E62) | last(nodes(p))] as mark',
            '[p in (insignia:E25)-[:P3]->(:E62) | last(nodes(p))] as insignia',
            '[p in (object:E22)-[:P3]->(:E62{name:"objectDescription"}) | last(nodes(p))] as description',
            '[p in (object:E22)-[:P42]-(:E55{name:"period"}) | last(nodes(p))] as period',
            '[p in (object:E22)-[:P2]-(:E55{name:"objectCategory"}) | last(nodes(p))] as category',
            '[p in (object:E22)-[:P62]-(:E38) | last(nodes(p))] as photograph',
            '[p in (object:E22)-[:P24]-(:E78) | last(nodes(p))] as collection',
            '[p in (object:E22)-[:P62]-(:E38)-[:P3]-(:E62) | last(nodes(p))] as photographCaption',
        ];

        $withStatements = array_merge($withStatement, $withProperties);
        $withStatements = array_unique($withStatements);

        $withStatement = implode(', ', $withStatements);

        $fullMatchStatement = $initialStatement;

        if (!empty($fullMatchStatement)) {
            $fullMatchStatement .= ', ' . $matchStatement;
            $fullMatchStatement = trim($fullMatchStatement);
        }

        $fullMatchStatement = rtrim($fullMatchStatement, ',');

        $query = "MATCH $fullMatchStatement
        WHERE $whereStatement";

        foreach ($optionalStatements as $optionalStatement) {
            $query .= ' OPTIONAL MATCH ' . $optionalStatement['match'] . ' WHERE ' . $optionalStatement['where'];
        }

        $returnProperties = [
            "distinct find",
            "embargo as embargo",
            "id(find) as identifier",
            "classifications",
            "typologyClassification",
            "findDate.value as findDate",
            "validation.value as validation",
            "person.email as email",
            "id(person) as finderId",
            "head(period).value as period",
            "head(material).value as material",
            "head(category).value as category",
            "head(technique).value as technique",
            "head(modification).value as modification",
            "head(mark).value as mark",
            "head(insignia).value as insignia",
            "head(complete).value as complete",
            "head(collection) as collection",
            "photograph",
            "head(photographCaption).value as photographCaption",
            "head(latitude).value as lat",
            "head(longitude).value as lng",
            "head(excavationLatitude).value as excavationLat",
            "head(excavationLongitude).value as excavationLng",
            "head(description).value as objectDescription",
            "head(locality).value as locality",
            "head(location).accuracy as accuracy",
            "head(location).geoGrid as grid",
            "excavationTitle.value as excavationTitle",
            "excavationLocation.value as excavationAddressLocality",
            "head(objectNr) as objectNr",
        ];

        $query .= " WITH $withStatement
        RETURN " . implode(', ', $returnProperties) . " ORDER BY $orderStatement SKIP $offset LIMIT $limit";

        if (!empty($startStatement)) {
            $query = $startStatement . $query;
        }

        return compact('query', 'variables');
    }

    /**
     * @param $filters
     * @param $orderBy
     * @param $orderFlow
     * @param $validationStatus
     * @return array
     * @throws \Exception
     */
    private function getQueryStatements(
        $filters,
        $orderBy,
        $orderFlow,
        $validationStatus,
        ?array $excludeOptionalStatements = []
    ) {
        $matchStatements = [];
        $whereStatements = [];

        $email = @$filters['myfinds'];

        $variables = [];

        $startStatement = '';

        if (!empty($filters['query'])) {
            // Replace the whitespace with the Lucene syntax for white spaces text queries
            $query = preg_replace('#\s+#', ' AND ', $filters['query']);

            $startStatement = "START object=node:node_auto_index('fulltext_description:(*" . $query . "*)') ";
        }

        // Non-personal find statement
        $initialStatement = '(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation), (find:E10)-[P4]-(findDate:E52)';

        if ($validationStatus == '*') {
            $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
        } else {
            if (!empty($validationStatus)) {
                $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
                $variables['validationStatus'] = $validationStatus;
            }
        }

        $withStatement = ['validation', 'person'];

        // In our query find.id is aliased as identifier
        $orderStatement = 'identifier ' . $orderFlow;

        if ($orderBy == 'period') {
            $matchStatements[] = '(object:E22)-[P42]-(period:E55)';
            $withStatement[] = 'period';
            $orderStatement = "period.value $orderFlow";
        } else {
            if ($orderBy == 'findDate') {
                $withStatement[] = 'findDate';
                $orderStatement = "findDate.value $orderFlow";
            }
        }

        foreach ($this->getFilterPropertyQueryStatements() as $property => $config) {
            if (isset($filters[$property])) {
                $matchStatements[] = $config['match'];

                if (!empty($config['where'])) {
                    $whereStatements[] = $config['where'];
                }

                $variables[$config['whereVariableName']] = $filters[$property];

                // If we have an integer value, convert the value we received from the request URI
                // Neo4j makes a strict distinction between integers and strings
                if (@$config['varType'] == 'int') {
                    $variables[$config['whereVariableName']] = (int)$filters[$property];
                }

                if (!empty($config['with']) && !in_array($config['with'], $this->getDefaultWithStatementProperties())) {
                    $withStatement[] = $config['with'];
                }
            }
        }

        if (!empty($email)) {
            if ($validationStatus == '*') {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
            } else {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
                $variables['validationStatus'] = $validationStatus;
            }
        }

        // Add the multi-tenancy statement
        $whereStatements[] = NodeService::getTenantWhereStatement(['object', 'find']);

        $matchStatement = implode(', ', $matchStatements);
        $whereStatement = implode(' AND ', $whereStatements);

        // Add the optional statements
        $availableOptionalStatements = [
            'person' => [
                'match' => '(find:E10)-[P29]-(person:person)',
                'where' => NodeService::getTenantWhereStatement(['find', 'person']),
            ],
            'typology' => [
                'match' => '(object:E22)-[r:P108]->(productionEvent:productionEvent)-[:P41]->(productionClassification:productionClassification)-[:P42]->(typologyClassification:E55), (productionClassification:productionClassification)-[:P2]-(pcvType:E55 {value: "Typologie"})',
                'where' => NodeService::getTenantWhereStatement(
                    ['object', 'productionEvent', 'productionClassification', 'typologyClassification']
                ),
                'with' => ['typologyClassification'],
            ],
            'excavationTitle' => [
                'match' => '(excavationEvent:A9)-[:P1]->(excavationTitle:E41)',
                'where' => NodeService::getTenantWhereStatement(['excavationEvent']
                    ) . ' AND excavationEvent.internalId = find.excavationId',
                'with' => ['excavationTitle'],
            ],
            'excavationLocation' => [
                'match' => '(excavationEvent:A9)-[:AP3]->(:E27)-[:P53]->(:E53)-[:P89]->(:E53)-[:P87]->(excavationLocation:E45{name:"locationAddressLocality"})',
                'where' => NodeService::getTenantWhereStatement(['excavationEvent']
                    ) . ' AND excavationEvent.internalId = find.excavationId',
                'with' => ['excavationLocation'],
            ],
            'volledigheid' => [
                'match' => '(object:E22)-[:P56]->(complete:E25)-[:P2]->(:E55 {value: "volledigheid"})',
                'where' => NodeService::getTenantWhereStatement(['complete']),
            ],
            'merkteken' => [
                'match' => '(object:E22)-[:P56]->(mark:E25)-[:P2]->(:E55 {value: "merkteken"})',
                'where' => NodeService::getTenantWhereStatement(['mark']),
            ],
            'opschrift' => [
                'match' => '(object:E22)-[:P56]->(insignia:E25)-[:P2]->(:E55 {value: "opschrift"})',
                'where' => NodeService::getTenantWhereStatement(['insignia']),
            ],
        ];

        $optionalStatements = [];

        foreach ($availableOptionalStatements as $optionalStatementName => $availableOptionalStatement) {
            if (in_array($optionalStatementName, $excludeOptionalStatements)) {
                continue;
            }

            $optionalStatements[] = $availableOptionalStatement;

            if (!empty($availableOptionalStatement['with'])) {
                $withStatement = array_merge($withStatement, $availableOptionalStatement['with']);
            }
        }

        return compact(
            'startStatement',
            'initialStatement',
            'optionalStatements',
            'matchStatement',
            'whereStatement',
            'withStatement',
            'orderStatement',
            'variables'
        );
    }

    /**
     * Return the supported filters for findEvent with the accompanying match and where statements
     *
     * The key of the filter properties map is the key that is normally passed down through the filters
     * array. This means you can look up filter names by index and fetch their configuration easily and quickly.
     * The whereVariableName property is the name of the variable that the where statement contains, this can be used
     * to pass down the name of the variable in the bindings of the query builder.
     *
     * @return array
     * @throws \Exception
     */
    public function getFilterPropertyQueryStatements()
    {
        return [
            'objectMaterial' => [
                'match' => '(object:E22)-[P45]-(material:E57)',
                'where' => 'material.value = {material} AND ' . NodeService::getTenantWhereStatement(['material']),
                'whereVariableName' => 'material',
                'with' => 'material',
            ],
            'technique' => [
                'match' => '(object:E22)-[producedBy:P108]-(pEvent:E12)-[P33]-(techniqueNode:E29)-[hasTechniquetype:P2]-(technique:E55)',
                'where' => 'technique.value = {technique} AND ' . NodeService::getTenantWhereStatement(['technique']),
                'whereVariableName' => 'technique',
                'with' => 'technique',
            ],
            'category' => [
                'match' => '(object:E22)-[categoryType:P2]-(category:E55)',
                'where' => 'category.value = {category} AND ' . NodeService::getTenantWhereStatement(['category']),
                'whereVariableName' => 'category',
                'with' => 'category',
            ],
            'period' => [
                'match' => '(object:E22)-[P42]-(period:E55)',
                'where' => 'period.value = {period} AND ' . NodeService::getTenantWhereStatement(['period']),
                'whereVariableName' => 'period',
                'with' => 'period',
            ],
            'findSpot' => [
                'match' => '(find:E10)-[P7]->(findSpot:E27)-[P2]->(findSpotType:E55)',
                'where' => 'findSpotType.value = {findSpotType} AND ' . NodeService::getTenantWhereStatement(
                        ['findSpotType']
                    ),
                'whereVariableName' => 'findSpotType',
                'with' => 'findSpotType',
            ],
            'modification' => [
                'match' => '(object:E22)-[treatedDuring:P108]->(treatmentEvent:E11)-[P33]->(modificationTechnique:E29)-[P2]->(modificationTechniqueType:E55)',
                'where' => 'modificationTechniqueType.value = {modificationTechniqueType} AND ' . NodeService::getTenantWhereStatement(
                        ['modificationTechniqueType']
                    ),
                'whereVariableName' => 'modificationTechniqueType',
                'with' => 'modificationTechniqueType',
            ],
            'embargo' => [
                'match' => '(object:E22)',
                'where' => 'object.embargo = {embargo} AND ' . NodeService::getTenantWhereStatement(['object']),
                'whereVariableName' => 'embargo',
                'with' => 'object',
            ],
            'id' => [
                'match' => '(find:E10)',
                'where' => 'id(find) = {id} AND ' . NodeService::getTenantWhereStatement(['find']),
                'whereVariableName' => 'id',
            ],
            'collection' => [
                'match' => '(object:E22)-[P24]-(collection:E78)',
                'where' => 'id(collection)= {collection} AND ' . NodeService::getTenantWhereStatement(['collection']),
                'whereVariableName' => 'collection',
                'with' => 'collection',
                'varType' => 'int',
            ],
            'photographCaption' => [
                'match' => '(object:E22)-[:P62]-(:E38)-[:P3]-(photographCaption:E62)',
                'whereVariableName' => '',
            ],
            'findSpotLocation' => [
                'match' => '(find:E10)-[:P7]->(:E27)-[:P53]->(:E53)-[:P89]->(:E53)-[:P87]->(locationAddressLocality:E45{name:"locationAddressLocality"})',
                'where' => 'locationAddressLocality.value = {findSpotLocation}',
                'whereVariableName' => 'findSpotLocation',
            ],
            'excavationLocation' => [
                'match' => '(object:E22)-[:P157]-(:S22)-[:P53]->(:E27)-[:P53]->(:E53)-[:P89]->(:E53)-[:P87]->(excavationLocation:E45{name:"locationAddressLocality"})',
                'where' => 'excavationLocation.value = {excavationLocation}',
                'whereVariableName' => 'excavationLocation',
            ],
            'volledigheid' => [
                'match' => '(object:E22)-[:P56]->(distinguishingFeature:E25)-[:P2]->(:E55 {value: "volledigheid"}), (distinguishingFeature:E25)-[:P3]->(distinguishingFeatureValueNode:E62)',
                'where' => 'NOT distinguishingFeatureValueNode.value IN ["Nee", "nee", "Neen", "neen", "Onbekend"] AND ' . NodeService::getTenantWhereStatement(
                        ['distinguishingFeature']
                    ),
                'whereVariableName' => 'distinguishingFeatureValueNode',
            ],
            'merkteken' => [
                'match' => '(object:E22)-[:P56]->(distinguishingFeature:E25)-[:P2]->(:E55 {value: "merkteken"}), (distinguishingFeature:E25)-[:P3]->(distinguishingFeatureValueNode:E62)',
                'where' => 'NOT distinguishingFeatureValueNode.value IN ["Nee", "nee", "Neen", "neen", "Onbekend"] AND ' . NodeService::getTenantWhereStatement(
                        ['distinguishingFeature']
                    ),
                'whereVariableName' => 'distinguishingFeatureValueNode',
            ],
            'opschrift' => [
                'match' => '(object:E22)-[:P56]->(distinguishingFeature:E25)-[:P2]->(:E55 {value: "opschrift"}), (distinguishingFeature:E25)-[:P3]->(distinguishingFeatureValueNode:E62)',
                'where' => 'NOT distinguishingFeatureValueNode.value IN ["Nee", "nee", "Neen", "neen", "Onbekend"] AND ' . NodeService::getTenantWhereStatement(
                        ['distinguishingFeature']
                    ),
                'whereVariableName' => 'distinguishingFeatureValueNode',
            ],
            'panid' => [
                'match' => '(object:E22)-[r:P108]->(productionEvent:productionEvent)-[:P41]->(productionClassification:productionClassification)-[:P42]->(productionClassificationValue:E55)',
                'where' => 'productionClassificationValue.value =~ {productionClassificationValue} AND ' . NodeService::getTenantWhereStatement(
                        [
                            'productionEvent',
                            'productionClassification',
                            'productionClassificationValue',
                        ]
                    ),
                'whereVariableName' => 'productionClassificationValue',
                'with' => 'object',
            ],
            'panids' => [
                'match' => '(object:E22)-[r:P108]->(productionEvent:productionEvent)-[:P41]->(productionClassification:productionClassification)-[:P42]->(productionClassificationValue:E55)',
                'where' => 'productionClassificationValue.value IN {panIdValues} AND ' . NodeService::getTenantWhereStatement(
                        [
                            'productionEvent',
                            'productionClassification',
                            'productionClassificationValue',
                        ]
                    ),
                'whereVariableName' => 'panIdValues',
                'with' => 'object',
            ],
        ];
    }

    /**
     * Get some basic numbers from the findEvents and related objects
     *
     * @return array
     * @throws \Exception
     */
    public function getStatistics()
    {
        // There could be finds & related objects but no classifications at all
        // if the query would take the match statements below as one statement this
        // would make the result return zero rows, even though finds have been registered
        // therefore a UNION is in place (which is way more efficient than adding an optional MATCH statement)
        $classificationWhereStatement = NodeService::getTenantWhereStatement(['classification']);
        $objectWhereStatement = NodeService::getTenantWhereStatement(['object']);
        $allFindsWhereStatement = NodeService::getTenantWhereStatement(['allFinds']);
        $allExcavationWhereStaement = NodeService::getTenantWhereStatement(['excavationEvent']);

        $countQuery = "
        MATCH (classification:productionClassification)
        WHERE $classificationWhereStatement
        WITH count(distinct classification) as count
        RETURN count
        UNION ALL
        MATCH (allFinds:findEvent)
        WHERE $allFindsWhereStatement
        WITH count(distinct allFinds) as count
        RETURN count
        UNION ALL
        MATCH (excavationEvent:excavationEvent)
        WHERE $allExcavationWhereStaement
        WITH count(distinct excavationEvent.internalId) as count
        return count
        UNION ALL
        MATCH (object:E22)-[objectVal:P2]->(validation)
        WHERE validation.name = 'objectValidationStatus' AND validation.value='Gepubliceerd' AND $objectWhereStatement
        RETURN count(distinct object) as count";

        $cypherQuery = new Query($this->getClient(), $countQuery);

        $resultSet = $cypherQuery->getResultSet();

        $statistics = [
            'finds' => 0,
            'validatedFinds' => 0,
            'excavations' => 0,
            'classifications' => 0,
        ];

        if ($resultSet->count() <= 0) {
            return $statistics;
        }

        $index = 0;

        foreach ($statistics as $key => $count) {
            $statistics[$key] = empty(@$resultSet[$index][0]) ? 0 : $resultSet[$index][0];

            $index++;
        }

        return $statistics;
    }

    /**
     * Parse the result set of the API cypher query
     *
     * @param  ResultSet $results
     *
     * @return array
     */
    private function parseFilteredFindsListResults(ResultSet $results)
    {
        $data = [];

        foreach ($results as $result) {
            $tmp = [
                'created_at' => $result['data']->getProperty('created_at'),
                'updated_at' => $result['data']->getProperty('updated_at'),
                'excavationId' => $result['data']->getProperty('excavationId'),
            ];

            foreach ($result as $key => $val) {
                if (!is_object($val)) {
                    $tmp[$key] = $val;
                } else {
                    if ($key == 'photograph' && $val->count()) {
                        $tmp[$key] = $val->current()->resized;

                        if (empty($tmp[$key])) {
                            $tmp[$key] = $val->current()->src;
                        }
                    } else {
                        if ($key == 'collection' && $val->getProperty('title')) {
                            $tmp['collectionTitle'] = $val->getProperty('title');
                        } else {
                            if ($key == 'classifications') {
                                $tmp['classificationCount'] = 0;
                                $classifications = [];

                                foreach ($val as $classification) {
                                    $classifications[] = $classification->getId();
                                }

                                $tmp['classificationCount'] = collect($classifications)->unique()->count();
                            } else {
                                if ($key == 'typologyClassification') {
                                    if (!is_array($val)) {
                                        $val = [$val];
                                    }

                                    // Get the PAN ID typology
                                    foreach ($val as $classification) {
                                        $classificationValue = $classification->getProperty('value');

                                        if (empty($classificationValue)) {
                                            continue;
                                        }

                                        // We have an issue where if the value is 0X (i.e. 02) the value is returned as 2
                                        // We need to have the proper typology code, so we handle that issue here, instead of fiddling
                                        // with the underlying library
                                        if (strlen($classificationValue) == 1) {
                                            $classificationValue = '0' . $classificationValue;
                                        }

                                        if (preg_match('#^\d{2}(-\d{2})*$#', $classificationValue)) {
                                            $tmp['panId'] = $classificationValue;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $data[] = $tmp;
        }

        $panIds = collect(array_pluck($data, 'panId') ?? [])
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $panTypologyInformation = app(PanTypologyRepository::class)->getPanTypologyInformationForIds($panIds);

        // Append PAN reference type information if applicable
        return collect($data)
            ->map(function ($result) use ($panTypologyInformation) {
                $result['_panTypologyInfo'] = @$panTypologyInformation[$result['panId']] ?? [];

                return $result;
            })
            ->toArray();
    }

    /**
     * @param  array $find
     * @return array
     */
    private function fetchExcavationInformation(array $find)
    {
        // Fetch the excavation UUID from the find and fetch the excavation information based on that
        $excavationUUID = array_get($find, 'excavationId');

        if (empty($excavationUUID)) {
            return [];
        }

        return app(ExcavationRepository::class)->getMetaDataForExcavation($excavationUUID);
    }

    /**
     * @param  array $find
     * @return array
     */
    private function fetchPanTypologyInformation(array $find)
    {
        // We assume that the only classification value is the PAN ID
        if (empty($find['panId'])) {
            return [];
        }

        return app(PanTypologyRepository::class)->getMetaForPanId($find['panId']);
    }

    /**
     * Get the exportable data points of a find event
     *
     * @param  integer $findId
     * @return array
     * @throws \Exception
     */
    public function getExportableData($findId)
    {
        $query = 'MATCH (find:E10)-[P12]-(object:E22)
        WHERE ' . NodeService::getTenantWhereStatement(['find', 'object'])
            . ' OPTIONAL MATCH (find:E10)-[P7]-(findSpot:E27)-[P53]-(location:E53), (location:E53)-[latRel:P87]-(lat:E47{name:"lat"}), (location:E53)-[lngRel:P87]-(lng:E47{name:"lng"}) '
            . ' WHERE ' . NodeService::getTenantWhereStatement(['find', 'findSpot', 'location', 'lat', 'lng']) .
            'OPTIONAL MATCH (find:E10)-[P29]-(person:person) ' . ' WHERE ' . NodeService::getTenantWhereStatement(
                ['find', 'person']
            ) .
            'OPTIONAL MATCH (object:E22)-[P42]-(period:E55{name:"period"}) ' . ' WHERE ' . NodeService::getTenantWhereStatement(
                ['object', 'period']
            ) .
            'OPTIONAL MATCH (object:E22)-[P2]-(category:E55{name:"objectCategory"}) ' . ' WHERE ' . NodeService::getTenantWhereStatement(
                ['object', 'category']
            ) .
            'OPTIONAL MATCH (object:E22)-[P45]-(material:E57{name:"objectMaterial"}) ' . ' WHERE ' . NodeService::getTenantWhereStatement(
                ['object', 'material']
            ) .
            ' WITH distinct find, category, period, material, person, lat, lng, location
        WHERE id(find) = {findId}
         RETURN id(find) as identifier, category.value as objectCategory, period.value as objectPeriod, material.value as objectMaterial,
        person.showNameOnPublicFinds as showName, person.lastName as lastName, person.firstName as firstName, person.detectoristNumber as detectoristNumber, lat.value as latitude,
        lng.value as longitude, location.accuracy as accuracy, find.created_at as created_at';

        $variables = [];
        $variables['findId'] = $findId;

        try {
            $cypherQuery = new Query($this->getClient(), $query, $variables);
            $results = $cypherQuery->getResultSet();

            if ($results->count() < 1) {
                return [];
            }

            $result = $results->current();

            $data = [];

            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }

            return $data;
        } catch (\Exception $ex) {
            \Log::error("Something went wrong while fetching exportable data: " . $ex->getMessage());
        }

        return [];
    }

    /**
     * Get all the nodes of a findEvent
     *
     * @param  int|null $limit
     * @param  int|null $offset
     * @return \Everyman\Neo4j\Query\Row
     * @throws \Exception
     */
    public function getAll(?int $limit = 100, ?int $offset = 0): \Everyman\Neo4j\Query\Row
    {
        $client = $this->getClient();

        $findLabel = $client->makeLabel($this->label);

        return NodeService::getNodesForLabel($findLabel, [], $limit, $offset);
    }

    /**
     * @return int
     */
    public function getCountOfAllFinds(): int
    {
        $client = $this->getClient();

        $findLabel = $client->makeLabel($this->label);

        return NodeService::getNodesCountForLabel($findLabel);
    }

    /**
     * Get the default properties that are present in the with statement
     *
     * @return array
     */
    private function getDefaultWithStatementProperties()
    {
        return [
            'classifications',
            'location',
            'latitude',
            'longitude',
            'locality',
            'material',
            'period',
            'category',
            'photograph',
            'collection',
        ];
    }

    /**
     * @param  int $findId
     * @return int|void
     */
    public function getRelatedObjectId(int $findId)
    {
        $query = 'MATCH (find:E10)-[P12]-(object:E22)
        WHERE id(find) = {findId} AND ' . NodeService::getTenantWhereStatement(['find', 'object']) .
            'RETURN id(object) as objectId';

        $cypherQuery = new Query($this->getClient(), $query, ['findId' => $findId]);
        $results = $cypherQuery->getResultSet();

        if ($results->count() < 1) {
            return;
        }

        $result = $results->current();

        return @$result['objectId'];
    }
}

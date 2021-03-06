<?php

namespace App\Repositories;

use App\Models\FindEvent;
use App\Models\ProductionClassification;
use App\NodeConstants;
use App\Services\NodeService;
use Everyman\Neo4j\Relationship;
use Everyman\Neo4j\Cypher\Query;

/**
 * Class FindRepository
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
     * @param $properties
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function store($properties)
    {
        $find = new FindEvent($properties);

        $find->save();

        return $find->getId();
    }

    /**
     * Get all of the finds for a person
     *
     * @param Person $person The Person object
     * @param integer $limit
     * @param integer $offset
     * @return array
     * @throws \Everyman\Neo4j\Exception
     */
    public function getForPerson($person, $limit = 20, $offset = 0)
    {
        // Get all of the related finds
        $personNode = $person->getNode();

        $relationships = $personNode->getRelationships(['P29'], Relationship::DirectionOut);

        // Apply paging by taking a slice of the relationships array
        $relationships = array_slice($relationships, $offset, $limit);

        $finds = [];

        foreach ($relationships as $relation) {
            $find_node = $relation->getEndNode();

            $findEvent = new FindEvent();
            $findEvent->setNode($find_node);

            // Get the entire data that's behind the find
            $find = $findEvent->getValues();

            $finds[] = $find;
        }

        return ['data' => $finds, 'count' => count($relationships)];
    }

    public function expandValues($findId, $user = null)
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
        $variables['findId'] = (int)$findId;

        $client = $this->getClient();

        $cypher_query = new Query($client, $query, $variables);
        $results = $cypher_query->getResultSet();

        if ($results->count() > 0) {
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
        }

        return $find;
    }

    /**
     * Return FindEvents that are filtered
     * We expect that all filters are object filters (e.g. category, period, technique, material)
     * We'll have to build our query based on the filters that are configured,
     * some are filters on object relationships, some on find event, some on classifications
     *
     * @param array $filters
     *
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param string $orderFlow
     * @param string $validationStatus
     * @return array
     */
    public function getAllWithFilter(
        $filters,
        $limit = 20,
        $offset = 0,
        $orderBy = 'findDate',
        $orderFlow = 'ASC',
        $validationStatus = '*'
    ) {
        extract($this->prepareFilteredQuery($filters, $limit, $offset, $orderBy, $orderFlow, $validationStatus));

        $cypherQuery = new Query($this->getClient(), $query, $variables);

        $data = $this->parseApiResults($cypherQuery->getResultSet());

        $count = $this->getCount($query, $variables);

        return ['data' => $data, 'count' => $count];
    }

    /**
     * Get a heatmap count for a filtered search
     *
     * @param array $filters
     * @param string $validationStatus
     * @return array
     */
    public function getHeatMap($filters, $validationStatus)
    {
        extract($this->getQueryStatements($filters, '', '', $validationStatus));

        $withStatement[] = 'find';
        $withStatement = array_unique($withStatement);
        $withStatement = implode(', ', $withStatement);

        $fullMatchStatement = $initialStatement;

        if (!empty($fullMatchStatement)) {
            $fullMatchStatement .= ', ' . $matchStatement;
            $fullMatchStatement = trim($fullMatchStatement);
        }

        $fullMatchStatement = rtrim($fullMatchStatement, ',');

        $query = "MATCH $fullMatchStatement, (find:E10)-[P7]->(findSpot:E27)-[P53]->(location:E53)
        WITH $withStatement, location
        WHERE $whereStatement
        RETURN count(distinct find) as findCount, location.geoGrid as centre";

        if (!empty($startStatement)) {
            $query = $startStatement . $query;
        }

        $cypherQuery = new Query($this->getClient(), $query, $variables);

        $heatMapResults = [];

        foreach ($cypherQuery->getResultSet() as $result) {
            $heatMapResults[] = [
                'count' => $result['findCount'],
                'gridCenter' => $result['centre']
            ];
        }

        return $heatMapResults;
    }

    /**
     * Prepare the filtered cypher query
     *
     *
     * @param array $filters
     * @param integer $limit
     * @param integer $offset
     * @param string $orderBy
     * @param string $orderFlow
     * @param string $validationStatus
     * @return array
     */
    private function prepareFilteredQuery($filters, $limit, $offset, $orderBy, $orderFlow, $validationStatus)
    {
        extract($this->getQueryStatements($filters, $orderBy, $orderFlow, $validationStatus));

        $withProperties = [
            'find',
            'findDate',
            'person',
            'validation',
            '[p in (object)-[:P108]-(:E12)-[:P41]-(:E17) | last(nodes(p))] as classifications',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53) | last(nodes(p))] as location',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53)-[:P87]-(:E47{name:"lat"}) | last(nodes(p))] as latitude',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53)-[:P87]-(:E47{name:"lng"}) | last(nodes(p))] as longitude',
            '[p in (find:E10)-[:P7]-(:E27)-[:P53]-(:E53)-[:P89]-(:E53)-[:P87]-(:locationAddressLocality)|last(nodes(p))] as locality',
            '[p in (object:E22)-[:P45]-(:E57) | last(nodes(p))] as material',
            '[p in (object:E22)-[:P42]-(:E55{name:"period"}) | last(nodes(p))] as period',
            '[p in (object:E22)-[:P2]-(:E55{name:"objectCategory"}) | last(nodes(p))] as category',
            '[p in (object:E22)-[:P62]-(:E38) | last(nodes(p))] as photograph',
            '[p in (object:E22)-[:P24]-(:E78) | last(nodes(p))] as collection',
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
        WHERE $whereStatement
        WITH $withStatement
        RETURN distinct find, id(find) as identifier, classifications, findDate.value as findDate, validation.value as validation, person.email as email, id(person) as finderId, head(period).value as period, head(material).value as material, head(category).value as category, head(collection) as collection, photograph, head(latitude).value as lat, head(longitude).value as lng, head(locality).value as locality, head(location).accuracy as accuracy, head(location).geoGrid as grid
        ORDER BY $orderStatement
        SKIP $offset
        LIMIT $limit";

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
    private function getQueryStatements($filters, $orderBy, $orderFlow, $validationStatus)
    {
        $matchStatements = [];
        $whereStatements = [];

        $filterProperties = $this->getFilterProperties();

        $email = @$filters['myfinds'];

        $variables = [];

        $startStatement = '';

        if (!empty($filters['query'])) {
            // Replace the whitespace with the lucene syntax for white spaces text queries
            $query = preg_replace('#\s+#', ' AND ', $filters['query']);

            $startStatement = "START object=node:node_auto_index('fulltext_description:(*" . $query . "*)') ";
        }

        // Non personal find statement
        $initialStatement = '(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation), (find:E10)-[P4]-(findDate:E52),(find:E10)-[P29]-(person:person)';

        // Check on validation status
        if ($validationStatus == '*') {
            $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
        } else {
            $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
            $variables['validationStatus'] = $validationStatus;
        }

        $withStatement = ['validation', 'person'];

        // In our query find.id is aliased as identifier
        $orderStatement = 'find.created_at ' . $orderFlow;

        if ($orderBy == 'period') {
            $matchStatements[] = '(object:E22)-[P42]-(period:E55)';
            $withStatement[] = 'period';
            $orderStatement = "period.value $orderFlow";
        } elseif ($orderBy == 'findDate') {
            $withStatement[] = 'findDate';
            $orderStatement = "findDate.value $orderFlow";
        }

        foreach ($filterProperties as $property => $config) {
            if (isset($filters[$property])) {
                $matchStatements[] = $config['match'];
                $whereStatements[] = $config['where'];
                $variables[$config['nodeName']] = $filters[$property];

                // If we have an integer value, convert the value we received from the request URI
                // Neo4j makes a strict distinction between integers and strings
                if (@$config['varType'] == 'int') {
                    $variables[$config['nodeName']] = (int)$filters[$property];
                }

                if (!empty($config['with']) && !in_array($config['with'], $this->getDefaultWithProperties())) {
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
        $whereStatements[] = NodeService::getTenantWhereStatement(['person', 'object', 'find']);

        $matchStatement = implode(', ', $matchStatements);
        $whereStatement = implode(' AND ', $whereStatements);

        return compact(
            'startStatement',
            'initialStatement',
            'matchStatement',
            'whereStatement',
            'withStatement',
            'orderStatement',
            'variables'
        );
    }

    /**
     * Get the default properties that are present in the with statement
     *
     * @return array
     */
    private function getDefaultWithProperties()
    {
        return ['classifications', 'location', 'latitude', 'longitude', 'locality', 'material', 'period', 'category', 'photograph', 'collection'];
    }

    /**
     * Return the supported filters for findEvent with the accompanying match and where statements
     *
     * The key of the filter properties map is the key that is normally passed down through the filters
     * array. This means you can look up filter names by index and fetch their configuration easily and quickly.
     * The nodeName property is the name of the variable that the where statement contains, this can be used
     * to pass down the name of the variable in the bindings of the query builder.
     *
     * @return array
     * @throws \Exception
     */
    public function getFilterProperties()
    {
        return [
            'objectMaterial' => [
                'match' => '(object:E22)-[P45]-(material:E57)',
                'where' => 'material.value = {material} AND ' . NodeService::getTenantWhereStatement(['material']),
                'nodeName' => 'material',
                'with' => 'material',
            ],
            'technique' => [
                'match' => '(object:E22)-[producedBy:P108]-(pEvent:E12)-[P33]-(techniqueNode:E29)-[hasTechniquetype:P2]-(technique:E55)',
                'where' => 'technique.value = {technique} AND ' . NodeService::getTenantWhereStatement(['technique']),
                'nodeName' => 'technique',
                'with' => 'technique',
            ],
            'category' => [
                'match' => '(object:E22)-[categoryType:P2]-(category:E55)',
                'where' => 'category.value = {category} AND ' . NodeService::getTenantWhereStatement(['category']),
                'nodeName' => 'category',
                'with' => 'category',
            ],
            'period' => [
                'match' => '(object:E22)-[P42]-(period:E55)',
                //"(object:E22)-[P106]-(pEvent:E12)-[P41]-(classification:E17)-[P42]-(period:E55)",
                'where' => 'period.value = {period} AND ' . NodeService::getTenantWhereStatement(['period']),
                'nodeName' => 'period',
                'with' => 'period',
            ],
            'findSpot' => [
                'match' => '(find:E10)-[P7]->(findSpot:E27)-[P2]->(findSpotType:E55)',
                'where' => 'findSpotType.value = {findSpotType} AND ' . NodeService::getTenantWhereStatement(['findSpotType']),
                'nodeName' => 'findSpotType',
                'with' => 'findSpotType',
            ],
            'modification' => [
                'match' => '(object:E22)-[treatedDuring:P108]->(treatmentEvent:E11)-[P33]->(modificationTechnique:E29)-[P2]->(modificationTechniqueType:E55)',
                'where' => 'modificationTechniqueType.value = {modificationTechniqueType} AND ' . NodeService::getTenantWhereStatement(['modificationTechniqueType']),
                'nodeName' => 'modificationTechniqueType',
                'with' => 'modificationTechniqueType'
            ],
            'embargo' => [
                'match' => '(object:E22)',
                'where' => 'object.embargo = {embargo} AND ' . NodeService::getTenantWhereStatement(['object']),
                'nodeName' => 'embargo',
                'with' => 'object'
            ],
            'collection' => [
                'match' => '(object:E22)-[P24]-(collection:E78)',
                'where' => 'id(collection)= {collection} AND ' . NodeService::getTenantWhereStatement(['collection']),
                'nodeName' => 'collection',
                'with' => 'collection',
                'varType' => 'int',
            ]
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
        $statistics = [
            'finds' => 0,
            'validatedFinds' => 0,
            'classifications' => 0,
        ];

        // There could be finds & related objects but no classifications at all
        // if the query would take the match statements below as one statement this
        // would make the result return zero rows, even though finds have been registered
        // therefore a UNION is in place (which is way more efficient than adding an optional MATCH statement)
        $classificationWhereStatement = NodeService::getTenantWhereStatement(['classification']);
        $objectWhereStatement = NodeService::getTenantWhereStatement(['object']);
        $allFindsWhereStatement = NodeService::getTenantWhereStatement(['allFinds']);

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
        MATCH (object:E22)-[objectVal:P2]->(validation)
        WHERE validation.name = 'objectValidationStatus' AND validation.value='Gepubliceerd' AND $objectWhereStatement
        RETURN count(distinct object) as count";

        $cypherQuery = new Query($this->getClient(), $countQuery);

        $resultSet = $cypherQuery->getResultSet();

        if ($resultSet->count() > 0) {
            $statistics['classifications'] = empty(@$resultSet[0][0]) ? 0 : $resultSet[0][0];
            $statistics['finds'] = empty(@$resultSet[1][0]) ? 0 : $resultSet[1][0];
            $statistics['validatedFinds'] = empty(@$resultSet[2][0]) ? 0 : $resultSet[2][0];
        }

        return $statistics;
    }

    /**
     * Get the count of a query by replacing the RETURN statement
     * This function assumes that find is declared in the query
     *
     * @param string $query A cypher query string
     * @param array $variables The variables and their variables for the query
     *
     * @return integer
     */
    private function getCount($query, $variables)
    {
        $chunks = explode('RETURN', $query);

        $statement = $chunks[0];

        $countQuery = $statement . ' RETURN count(distinct find) as findCount';
        $countQuery = new Query($this->getClient(), $countQuery, $variables);

        $results = $countQuery->getResultSet();

        $countResult = $results->current();

        return $countResult['findCount'];
    }

    /**
     * Parse the result set of the API cypher query
     *
     * @param ResultSet $results
     *
     * @return array
     */
    private function parseApiResults($results)
    {
        $data = [];

        foreach ($results as $result) {
            $tmp = [
                'created_at' => $result['data']->getProperty('created_at'),
                'updated_at' => $result['data']->getProperty('updated_at'),
            ];

            foreach ($result as $key => $val) {
                if (!is_object($val)) {
                    $tmp[$key] = $val;
                } elseif ($key == 'photograph' && $val->count()) {
                    $tmp[$key] = $val->current()->resized;
                } elseif ($key == 'collection' && $val->getProperty('title')) {
                    $tmp['collectionTitle'] = $val->getProperty('title');
                } elseif ($key == 'classifications') {
                    $tmp['classificationCount'] = 0;
                    $classifications = [];

                    foreach ($val as $classification) {
                        $classifications[] = $classification->getId();
                    }

                    $tmp['classificationCount'] = collect($classifications)->unique()->count();
                }
            }

            $data[] = $tmp;
        }

        return $data;
    }

    /**
     * Get the exportable data points of a find event
     *
     * @param integer $findId
     * @return array
     * @throws \Exception
     */
    public function getExportableData($findId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['person', 'find', 'category', 'material', 'object']);

        $query = 'MATCH (find:E10)-[P12]-(object:E22)
        WHERE ' . NodeService::getTenantWhereStatement(['find', 'object'])
        . ' OPTIONAL MATCH (find:E10)-[P7]-(findSpot:E27)-[P53]-(location:E53), (location:E53)-[latRel:P87]-(lat:E47{name:"lat"}), (location:E53)-[lngRel:P87]-(lng:E47{name:"lng"}) '
        . ' WHERE ' . NodeService::getTenantWhereStatement(['find', 'findSpot', 'location', 'lat', 'lng']) .
        'OPTIONAL MATCH (find:E10)-[P29]-(person:person) ' . ' WHERE ' . NodeService::getTenantWhereStatement(['find', 'person']) .
        'OPTIONAL MATCH (object:E22)-[P42]-(period:E55{name:"period"}) ' . ' WHERE ' . NodeService::getTenantWhereStatement(['object', 'period']) .
        'OPTIONAL MATCH (object:E22)-[P2]-(category:E55{name:"objectCategory"}) ' . ' WHERE ' . NodeService::getTenantWhereStatement(['object', 'category']) .
        'OPTIONAL MATCH (object:E22)-[P45]-(material:E57{name:"objectMaterial"}) ' . ' WHERE ' . NodeService::getTenantWhereStatement(['object', 'material']) .
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
     * Get all the bare nodes of a findEvent
     *
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     * @throws \Everyman\Neo4j\Exception
     * @throws \Exception
     */
    public function getAll()
    {
        $client = $this->getClient();

        $findLabel = $client->makeLabel($this->label);

        $findNodes = NodeService::getNodesForLabel($findLabel);

        return $findNodes;
    }
}

<?php

namespace App\Repositories;

use App\Models\FindEvent;
use App\Models\ProductionClassification;
use Everyman\Neo4j\Relationship;
use Everyman\Neo4j\Cypher\Query;

class FindRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(FindEvent::$NODE_TYPE, FindEvent::class);
    }

    public function store($properties)
    {
        $find = new FindEvent($properties);

        $find->save();

        return $find->getId();
    }

    /**
     * Get all of the finds for a person
     *
     * @param Person  $person The Person object
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
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

        // Add the vote of the user
        if (! empty($user)) {
            // Get the vote of the user for the find
            $query = 'MATCH (person:person)-[r]-(classification:productionClassification)-[*2..3]-(find:E10) WHERE id(person) = {userId} AND id(find) = {findId} RETURN r';

            $variables = [];
            $variables['userId'] = (int) $user->id;
            $variables['findId'] = (int) $findId;

            $client = $this->getClient();

            $cypher_query = new Query($client, $query, $variables);
            $results = $cypher_query->getResultSet();

            if ($results->count() > 0) {
                foreach ($results as $result) {
                    $relationship = $result->current();

                    $classification_id = $relationship->getEndNode()->getId();

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
     * @return
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
     * @param  array  $filters
     * @param  string $validationStatus
     * @return array
     */
    public function getHeatMap($filters, $validationStatus)
    {
        extract($this->getQueryStatements($filters, '', '', $validationStatus));

        $withStatement = implode(', ', $withStatement);

        $fullMatchStatement = $initialStatement;

        if (! empty($fullMatchStatement)) {
            $fullMatchStatement .= ', ' . $matchStatement;
            $fullMatchStatement = trim($fullMatchStatement);
        }

        $fullMatchStatement = rtrim($fullMatchStatement, ',');

        $query = "MATCH $fullMatchStatement, (find:E10)-[P7]->(findSpot:E27)-[P53]->(location:E53)
        WITH $withStatement, location
        WHERE $whereStatement
        RETURN count(distinct find) as findCount, location.geoGrid as centre";

        if (! empty($startStatement)) {
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
     * @param  array   $filters
     * @param  integer $limit
     * @param  integer $offset
     * @param  string  $orderBy
     * @param  string  $orderFlow
     * @param  string  $validationStatus
     * @return array
     */
    private function prepareFilteredQuery($filters, $limit, $offset, $orderBy, $orderFlow, $validationStatus)
    {
        extract($this->getQueryStatements($filters, $orderBy, $orderFlow, $validationStatus));

        $withProperties = [
           'distinct find',
           'validation',
           'findDate',
           'locality',
           'person',
           'count(distinct pClass) as pClassCount',
           'lat',
           'lng',
           'material',
           'category',
           'period',
           'photograph',
           'location',
        ];

        $withStatements = array_merge($withStatement, $withProperties);
        $withStatements = array_unique($withStatements);

        $withStatement = implode(', ', $withStatements);

        $fullMatchStatement = $initialStatement;

        if (! empty($fullMatchStatement)) {
            $fullMatchStatement .= ', ' . $matchStatement;
            $fullMatchStatement = trim($fullMatchStatement);
        }

        $fullMatchStatement = rtrim($fullMatchStatement, ',');

        $query = "MATCH $fullMatchStatement
        OPTIONAL MATCH (object:E22)-[producedBy:P108]-(productionEvent:E12)-[P41]-(pClass:E17)
        OPTIONAL MATCH (find:E10)-[P7]-(findSpot:E27)-[P53]-(location:E53)-[P89]-(address:E53), (address:E53)-[localityRel:P87]-(locality:locationAddressLocality), (location:E53)-[latRel:P87]-(lat:E47{name:\"lat\"}), (location:E53)-[lngRel:P87]-(lng:E47{name:\"lng\"})
        OPTIONAL MATCH (object:E22)-[P45]-(material:E57)
        OPTIONAL MATCH (object:E22)-[P42]-(period:E55{name:\"period\"})
        OPTIONAL MATCH (object:E22)-[P2]-(category:E55{name:\"objectCategory\"})
        OPTIONAL MATCH (object:E22)-[P62]-(photograph:E38)
        WITH $withStatement
        WHERE $whereStatement
        RETURN distinct find, id(find) as identifier, findDate.value as findDate, locality.value as locality, validation.value as validation, person.email as email, id(person) as finderId, pClassCount as classificationCount, lat.value as lat, lng.value as lng, material.value as material, category.value as category, period.value as period, collect(photograph.resized) as photograph, location.accuracy as accuracy, location.geoGrid as grid
        ORDER BY $orderStatement
        SKIP $offset
        LIMIT $limit";

        if (! empty($startStatement)) {
            $query = $startStatement . $query;
        }

        return compact('query', 'variables');
    }

    private function getQueryStatements($filters, $orderBy, $orderFlow, $validationStatus)
    {
        $matchStatements = [];
        $whereStatements = [];

        $filterProperties = $this->getFilterProperties();

        $email = @$filters['myfinds'];

        $variables = [];

        $startStatement = '';

        if (! empty($filters['query'])) {
            // Replace the whitespace with the lucene syntax for white spaces text queries
            $query = preg_replace('#\s+#', ' AND ', $filters['query']);

            $startStatement = "START object=node:node_auto_index('fulltext_description:(*" . $query . "*)') ";
        }

        // Non personal find statement
        $initialStatement = '(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation), (find:E10)-[P4]-(findDate:E52),(find:E10)-[P29]-(person:person)';

        // Check on validationstatus
        if ($validationStatus == '*') {
            $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
        } else {
            $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
            $variables['validationStatus'] = $validationStatus;
        }

        $withStatement = ['distinct find', 'validation', 'person'];

        // In our query find.id is aliased as identifier
        $orderStatement = 'identifier ' . $orderFlow;

        if ($orderBy == 'period') {
            $matchStatements[] = '(object:E22)-[P42]-(period:E55)';
            $withStatement[] = 'period';
            $orderStatement = "period.value $orderFlow";
        } elseif ($orderBy == 'findDate') {
            //$matchStatements[] = "(find:E10)-[P4]-(findDate:E52)"; // Is already part of the initial statement
            $withStatement[] = 'findDate';
            $orderStatement = "findDate.value $orderFlow";
        }

        foreach ($filterProperties as $property => $config) {
            if (! empty($filters[$property])) {
                $matchStatements[] = $config['match'];
                $whereStatements[] = $config['where'];
                $variables[$config['nodeName']] = $filters[$property];

                if (! empty($config['with'])) {
                    $withStatement[] = $config['with'];
                }
            }
        }

        if (! empty($email)) {
            if ($validationStatus == '*') {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
            } else {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
                $variables['validationStatus'] = $validationStatus;
            }
        }

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
     * Return the supported filters for findEvent with the accompanying match and where statements
     *
     * The key of the filter properties map is the key that is normally passed down through the filters
     * array. This means you can look up filternames by index and fetch their configuration easily and quickly.
     * The nodeName property is the name of the variable that the where statement contains, this can be used
     * to pass down the name of the varaible in the bindings of the query builder.
     *
     * @return array
     */
    public function getFilterProperties()
    {
        return [
            'objectMaterial' => [
                'match' => '(object:E22)-[P45]-(material:E57)',
                'where' => 'material.value = {material}',
                'nodeName' => 'material',
                'with' => 'material',
            ],
            'technique' => [
                'match' => '(object:E22)-[producedBy:P108]-(pEvent:E12)-[P33]-(techniqueNode:E29)-[hasTechniquetype:P2]-(technique:E55)',
                'where' => 'technique.value = {technique}',
                'nodeName' => 'technique',
                'with' => 'technique',
            ],
            'category' => [
                'match' => '(object:E22)-[categoryType:P2]-(category:E55)',
                'where' => 'category.value = {category}',
                'nodeName' => 'category',
                'with' => 'category',
            ],
            'period' => [
                'match' => '(object:E22)-[P42]-(period:E55)',
                //"(object:E22)-[P106]-(pEvent:E12)-[P41]-(classification:E17)-[P42]-(period:E55)",
                'where' => 'period.value = {period}',
                'nodeName' => 'period',
                'with' => 'period',
            ],
            'findSpot' => [
                'match' => '(find:E10)-[P7]->(findSpot:E27)-[P2]->(findSpotType:E55)',
                'where' => 'findSpotType.value = {findSpotType}',
                'nodeName' => 'findSpotType',
                'with' => 'findSpotType',
            ],
            'modification' => [
                'match' => '(object:E22)-[treatedDuring:P108]->(treatmentEvent:E11)-[P33]->(modificationTechnique:E29)-[P2]->(modificationTechniqueType:E55)',
                    'where' => 'modificationTechniqueType.value = {modificationTechniqueType}',
                    'nodeName' => 'modificationTechniqueType',
                    'with' => 'modificationTechniqueType'
            ]
        ];
    }

    /**
     * Get some basic numbers from the findEvents and related objects
     *
     * @return array
     */
    public function getStatistics()
    {
        $statistics = [
            'finds' => 0,
            'validatedFinds' => 0,
            'classifications' => 0,
        ];

        // There could be finds (and thus objects) but no classifications at all
        // if the query would take the match statements below as one statement this
        // would make the result return zero rows, even though finds have been registered
        // therefore a UNION is in place (which is way more efficient than adding an optional MATCH statement)
        $countQuery = "
        MATCH (classification:productionClassification)
        WITH count(distinct classification) as count
        RETURN count
        UNION ALL
        MATCH (allFinds:findEvent)
        WITH count(distinct allFinds) as count
        RETURN count
        UNION ALL
        MATCH (object:E22)-[objectVal:P2]->(validation)
        WHERE validation.name = 'objectValidationStatus' AND validation.value='Gepubliceerd'
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
     * @param string $query     A cypher query string
     * @param array  $variables The variables and their variables for the query
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
                if (! is_object($val)) {
                    $tmp[$key] = $val;
                } elseif ($key == 'photograph' && $val->count()) {
                    $tmp[$key] = $val->current();
                }
            }

            $data[] = $tmp;
        }

        return $data;
    }

    /**
     * Get the exportable data points of a find event
     *
     * @param  integer $findId
     * @return array
     */
    public function getExportableData($findId)
    {
        $query = 'MATCH (find:E10)-[P12]-(object:E22)
        OPTIONAL MATCH (find:E10)-[P7]-(findSpot:E27)-[P53]-(location:E53), (location:E53)-[latRel:P87]-(lat:E47{name:"lat"}), (location:E53)-[lngRel:P87]-(lng:E47{name:"lng"})
        OPTIONAL MATCH (find:E10)-[P29]-(person:person)
        OPTIONAL MATCH (object:E22)-[P42]-(period:E55{name:"period"})
        OPTIONAL MATCH (object:E22)-[P2]-(category:E55{name:"objectCategory"})
        OPTIONAL MATCH (object:E22)-[P45]-(material:E57{name:"objectMaterial"})
        WITH distinct find, category, period, material, person, lat, lng, location
        WHERE id(find) = {findId}
        RETURN id(find) as identifier, category.value as objectCategory, period.value as objectPeriod, material.value as objectMaterial,
        person.showNameOnPublicFinds as showName, person.lastName as lastName, person.firstName as firstName, person.detectoristNumber as detectoristNumber, lat.value as latitude,
        lng.value as longitude, location.accuracy as accuracy, find.created_at as created_at';

        $variables = [];
        $variables['findId'] = $findId;

        $cypherQuery = new Query($this->getClient(), $query, $variables);
        $results = $cypherQuery->getResultSet();

        $result = $results->current();

        $data = [];

        foreach ($result as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Get all the bare nodes of a findEvent
     *
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function getAll()
    {
        $client = $this->getClient();

        $findLabel = $client->makeLabel($this->label);

        $findNodes = $findLabel->getNodes();

        return $findNodes;
    }
}

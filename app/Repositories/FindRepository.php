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
        if (!empty($user)) {
            // Get the vote of the user for the find
            $query = "MATCH (person:person)-[r]-(classification:productionClassification)-[*2..3]-(find:E10) WHERE id(person) = {userId} AND id(find) = {findId} RETURN r";

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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @param array $filters
     *
     * @return
     */
    public function getAllWithFilter(
        $filters,
        $limit = 50,
        $offset = 0,
        $orderBy = 'findDate',
        $orderFlow = 'ASC',
        $validationStatus = '*'
    ) {
        extract($this->prepareFilteredQuery($filters, $limit, $offset, $orderBy, $orderFlow, $validationStatus));

        $cypherQuery = new Query($this->getClient(), $query, $variables);
        $data = $this->parseApiResults($cypherQuery->getResultSet());

        return $data;
    }

    /**
     * Get a heatmap count for a filtered search
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @param  array $filters
     * @param  string $validationStatus
     * @return array
     */
    public function getHeatMap($filters, $validationStatus)
    {
        extract($this->getQueryStatements($filters, '', '', $validationStatus));

        $query = "MATCH $initialStatement, $matchstatement, (find:E10)-[P7]->(findSpot:E27)-[P53]->(location:E53)
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @param  array $filters
     * @param  integer $limit
     * @param  integer $offset
     * @param  string $orderBy
     * @param  string $orderFlow
     * @param  string $validationStatus
     * @return array
     */
    private function prepareFilteredQuery($filters, $limit, $offset, $orderBy, $orderFlow, $validationStatus)
    {
        extract($this->getQueryStatements($filters, $orderBy, $orderFlow, $validationStatus));

        $query = "MATCH $initialStatement, $matchstatement
        WITH $withStatement
        ORDER BY $orderStatement
        WHERE $whereStatement
        RETURN distinct find, findCount
        SKIP $offset
        LIMIT $limit";

        if (!empty($startStatement)) {
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

        if (!empty($filters['query'])) {
            // Replace the whitespace with the lucene syntax for white spaces text queries
            $query = preg_replace('#\s+#', ' AND ', $filters['query']);

            $startStatement = "START object=node:node_auto_index('fulltext_description:(*" . $query . "*)') ";
        }

        // Non personal find statement
        $initialStatement = "(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation)";

        // Check on validationstatus
        if ($validationStatus == '*') {
            $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
        } else {
            $whereStatements[] = "validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
            $variables['validationStatus'] = $validationStatus;
        }

        $withStatement = "find, validation";

        $orderStatement = 'find.id DESC';

        if ($orderBy == 'period') {
            $matchStatements[] = "(object:E22)-[P42]-(period:E55)";//"(object:E22)-[P106]-(pEvent:E12)-[P41]-(classification:E17)-[P42]-(period:E55)";
            $withStatement .= ", period";
            $orderStatement = "period.value $orderFlow";
        } else {
            $matchStatements[] = "(find:E10)-[P4]-(findDate:E52)";
            $withStatement .= ", findDate";
            $orderStatement = "findDate.value $orderFlow";
        }

        foreach ($filterProperties as $property => $config) {
            if (!empty($filters[$property])) {
                $matchStatements[] = $config['match'];
                $whereStatements[] = $config['where'];
                $variables[$config['nodeName']] = $filters[$property];

                if (!empty($config['with'])) {
                    $withStatement .= ', ' . $config['with'];
                }
            }
        }

        if (!empty($email)) {
            $initialStatement = "(person:E21)-[P29]->(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation)";
            $withStatement .= ", person";

            if ($validationStatus == '*') {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
            } else {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
                $variables['validationStatus'] = $validationStatus;
            }
            // Can be deleted
            //$withStatement .= "find, validation";
        }

        $matchstatement = implode(', ', $matchStatements);
        $whereStatement = implode(' AND ', $whereStatements);
        $withStatement .= ", count(distinct find) as findCount";

        return compact(
            'startStatement',
            'initialStatement',
            'matchstatement',
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
                'match' => "(object:E22)-[P45]-(material:E57)",
                'where' => "material.value = {material}",
                'nodeName' => 'material',
                'with' => 'material',
            ],
            'technique' => [
                'match' => "(object:E22)-[P108]-(pEvent:E12)-[P33]-(technique:E29)-[techniqueType:P2]-(type:E55)",
                'where' => "type.value = {technique}",
                'nodeName' => 'technique',
                'with' => 'type',
            ],
            'category' => [
                'match' => "(object:E22)-[categoryType:P2]-(category:E55)",
                'where' => "category.value = {category}",
                'nodeName' => 'category',
                'with' => 'category',
            ],
            'period' => [
                'match' => "(object:E22)-[P42]-(period:E55)",
                //"(object:E22)-[P106]-(pEvent:E12)-[P41]-(classification:E17)-[P42]-(period:E55)",
                'where' => "period.value = {period}",
                'nodeName' => 'period',
                'with' => 'period',
            ],
            'findSpot' => [
                'match' => '(find:E10)-[P7]->(findSpot:E27)-[P2]->(findSpotType:E55)',
                'where' => 'findSpotType.value = {findSpotType}',
                'nodeName' => 'findSpotType',
                'with' => 'findSpotType',
            ],
            'modificationTechniqueType' => [
                'match' => '(object:E22)-[P108]->(treatmentEvent:E11)-[P33]->(modificationTechnique:E29)-[P2]->(modificationTechniqueType:E55)',
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

        $countQuery = "MATCH (allFinds:findEvent), (object:E22)-[objectVal:P2]->(validation),
        (classification:productionClassification)
        WITH count(distinct allFinds) as findCount, count(distinct object) as validatedFindCount,
        count(distinct classification) as classificationCount, validation
        WHERE validation.name = 'objectValidationStatus' AND validation.value='gevalideerd'
        RETURN findCount, validatedFindCount, classificationCount";

        $cypherQuery = new Query($this->getClient(), $countQuery);

        $resultSet = $cypherQuery->getResultSet();

        if ($resultSet->count() > 0) {
            // There's only one row as a result
            $row = $resultSet->current();
            $statistics['finds'] = $row['findCount'];
            $statistics['validatedFinds'] = $row['validatedFindCount'];
            $statistics['classifications'] = $row['classificationCount'];
        }

        return $statistics;
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
        $count = 0;

        foreach ($results as $result) {
            $find = new FindEvent();
            $node = $result['find'];
            $count = $result['findCount'];

            $find->setNode($node);
            $find = $find->getValues();

            $data[] = $find;
        }

        return [
            'data' => $data,
            'count' => $count
        ];
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

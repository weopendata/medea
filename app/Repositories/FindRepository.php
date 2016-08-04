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

        return $find;
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
            $variables['userId'] = $user->id;
            $variables['findId'] = $findId;

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
        $limit = 50,
        $offset = 0,
        $orderBy = 'findDate',
        $orderFlow = 'ASC',
        $validationStatus = '*'
    ) {
        $matchStatements = [];
        $whereStatements = [];

        $filterProperties = $this->getFilterProperties();

        $email = @$filters['myfinds'];

        $variables = [];

        // Non personal find statement
        $initialStatement = "(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation)";

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
            }
        }

        if (!empty($email)) {
            $initialStatement = "(person:E21)-[P29]->(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation)";
            if ($validationStatus == '*') {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
            } else {
                $whereStatements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value = {validationStatus}";
                $variables['validationStatus'] = $validationStatus;
            }
            $withStatement = "find, validation";
        }

        $matchstatement = implode(', ', $matchStatements);
        $whereStatement = implode(' AND ', $whereStatements);

        $query = "MATCH $initialStatement, $matchstatement
        WITH $withStatement
        ORDER BY $orderStatement
        WHERE $whereStatement
        RETURN distinct find
        SKIP $offset
        LIMIT $limit";

        $countquery = "MATCH $initialStatement, $matchstatement
        WITH $withStatement
        ORDER BY $orderStatement
        WHERE $whereStatement
        RETURN count(distinct find)";

        $cypherQuery = new Query($this->getClient(), $query, $variables);
        $data = $this->parseResults($cypherQuery);

        $cypherQuery = new Query($this->getClient(), $countquery, $variables);
        $count_results = $cypherQuery->getResultSet();

        $count = 0;

        if (!empty($count_results)) {
            $row = $count_results->current();
            $count = $row['count(distinct find)'];
        }

        return ['data' => $data, 'count' => $count];
    }

    /**
     * Return the supported filters for findEvents
     * with the accompanying match and where statements
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
            ],
            'technique' => [
                'match' => "(object:E22)-[P108]-(pEvent:E12)-[P33]-(technique:E29)-[techniqueType:P2]-(type:E55)",
                'where' => "type.value = {technique}",
                'nodeName' => 'technique',
            ],
            'category' => [
                'match' => "(object:E22)-[categoryType:P2]-(category:E55)",
                'where' => "category.value = {category}",
                'nodeName' => 'category',
            ],
            'period' => [
                'match' => "(object:E22)-[P42]-(period:E55)",
                //"(object:E22)-[P106]-(pEvent:E12)-[P41]-(classification:E17)-[P42]-(period:E55)",
                'where' => "period.value = {period}",
                'nodeName' => 'period',
            ],
        ];
    }

    private function parseResults($cypherQuery)
    {
        $results = $cypherQuery->getResultSet();
        $data = [];

        foreach ($results as $result) {
            $find = new FindEvent();
            $node = $result->current();
            $find->setNode($node);
            $find = $find->getValues();

            $data[] = $find;
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

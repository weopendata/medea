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
        $person_node = $person->getNode();

        $find_relations = $person_node->getRelationships(['P29'], Relationship::DirectionOut);

        $finds = [];

        foreach ($find_relations as $relation) {
            $find_node = $relation->getEndNode();

            $find_event = new FindEvent();
            $find_event->setNode($find_node);

            // Get the entire data that's behind the find
            $finds[] = $find_event->getValues();
        }

        return $finds;
    }

    public function expandValues($find_id, $user = null)
    {
        $find = parent::expandValues($find_id);

        // Add the vote of the user
        if (!empty($user)) {
            // Get the vote of the user for the find
            $query = "MATCH (person:person)-[r]-(classification:productionClassification)-[*2..3]-(find:E10) WHERE id(person) = $user->id AND id(find) = $find_id RETURN r";

            $client = $this->getClient();

            $cypher_query = new Query($client, $query);
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
     *
     * @param array $filters
     *
     * @return
     */
    public function getAllWithFilter($filters, $limit = 50, $offset = 0, $order_by = 'findDate', $order_flow = 'ASC', $validation_status = '*')
    {
        // We expect that all filters are object filters (e.g. category, culture, technique, material)
        // We'll have to build our query based on the filters that are configured,
        // some are filters on object relationships, some on find event, some on classifications
        $match_statements = [];
        $where_statements = [];

        $material = @$filters['material'];
        $technique = @$filters['technique'];
        $category = @$filters['category'];
        $culture = @$filters['culture'];
        $email = @$filters['myfinds'];

        // Non personal find statement
        $initial_statement = "(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation)";

        if ($validation_status == '*') {
            $where_statements[] = "validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
        } else {
            $where_statements[] = "validation.name = 'objectValidationStatus' AND validation.value = '$validation_status'";
        }

        $with_statement = "find, validation";

        $order_statement = 'find.id DESC';

        if ($order_by == 'culture') {
            $match_statements[] = "(object:E22)-[P106]-(pEvent:E12)-[P41]-(classification:E17)-[P42]-(culture:E55)";
            $with_statement .= ", culture";
            $order_statement = "culture.value $order_flow";
        } else {
            $match_statements[] = "(find:E10)-[P4]-(findDate:E52)";
            $with_statement .= ", findDate";
            $order_statement = "findDate.value $order_flow";
        }

        if (!empty($material)) {
            $match_statements[] = "(object:E22)-[P45]-(material:E57)";
            $where_statements[] = "material.value = '$material'";
        }

        if (!empty($technique)) {
            $match_statements[] = "(object:E22)-[P108]-(pEvent:E12)-[P33]-(technique:E29)-[techniqueType:P2]-(type:E55)";
            $where_statements[] = "type.value = '$technique'";
        }

        if (!empty($culture)) {
            $match_statements[] = "(object:E22)-[P106]-(pEvent:E12)-[P41]-(classification:E17)-[P42]-(culture:E55)";
            $where_statements[] = "culture.value = '$culture'";
        }

        if (!empty($category)) {
            $match_statements[] = "(object:E22)-[categoryType:P2]-(category:E55)";
            $where_statements[] = "category.value = '$category'";
        }

        if (!empty($email)) {
            $initial_statement = "(person:E21)-[P29]->(find:E10)-[P12]-(object:E22)-[objectVal:P2]-(validation)";
            if ($validation_status == '*') {
                $where_statements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value =~ '.*'";
            } else {
                $where_statements[] = "person.email = '$email' AND validation.name = 'objectValidationStatus' AND validation.value = '$validation_status'";
            }
            $with_statement = "find, validation";
        }

        $client = $this->getClient();

        $match_statement = implode(', ', $match_statements);
        $where_statement = implode(' AND ', $where_statements);

        $query = "MATCH $initial_statement, $match_statement
        WITH $with_statement
        ORDER BY $order_statement
        WHERE $where_statement
        RETURN distinct find
        SKIP $offset
        LIMIT $limit";

        $count_query = "MATCH $initial_statement, $match_statement
        WITH $with_statement
        ORDER BY $order_statement
        WHERE $where_statement
        RETURN count(distinct find)";

        $cypher_query = new Query($client, $query);
        $results = $cypher_query->getResultSet();

        $cypher_query = new Query($client, $count_query);
        $count_results = $cypher_query->getResultSet();

        $data = [];
        $count = 0;

        foreach ($results as $result) {
            $find = new FindEvent();
            $find->setNode($result->current());
            $data[] = $find->getValues();
        }

        if (!empty($count_results)) {
            $row = $count_results->current();
            $count = $row['count(distinct find)'];
        }

        return ['data' => $data, 'count' => $count];
    }
}

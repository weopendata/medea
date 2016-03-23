<?php

namespace App\Repositories;

use App\Models\Object;
use App\Models\ProductionEvent;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Relationship;

class ObjectRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Object::$NODE_TYPE, Object::class);
    }

    public function store($properties = [])
    {
        // Create and save a new object
        $object = new Object($properties);

        $object->save();

        return $object;
    }

    /**
     * Add a classification to an object
     *
     * @param $id             integer The id of the object
     * @param $classification array   The configuration of the classification
     *
     * @return Node
     */
    public function addClassification($id, $classification)
    {
        $object = $this->getById($id);

        if (!empty($object)) {
            $production_event = new ProductionEvent(['classification' => $classification]);
            $production_event->save();

            $object->relateTo($production_event->getNode(), 'P108')->save();

            return $object;
        }

        return null;
    }

    public function getClassification($id, $classification_id)
    {
        // To make this more neat, we'll use a specific Cypher query to bypass the
        // productionEvent link that lies between object and classification
        $query = "match (n)-[*2..2]-(classification) where id(n) = $id AND id(classification) = $classification_id return classification";

        $client = $this->getClient();

        $cypher_query = new Query($client, $query);
        $result = $cypher_query->getResultSet();

        if (!empty($result->current())) {
            return $result->current()->current();
        } else {
            return null;
        }
    }

    /**
     * Set the validation status of a certain object
     *
     * @param $id     integer The id of the object
     * @param $status string  The new status of the object
     *
     * @return Node
     */
    public function setValidationStatus($id, $status)
    {
        $object_node = $this->getById($id);

        $relationships = $object_node->getRelationships(['P2'], Relationship::DirectionOut);

        foreach ($relationships as $relationship) {
            $type_node = $relationship->getEndNode();

            $relationship->delete();
            $type_node->delete();
        }

        $object = new Object();
        $object->setNode($object_node);

        $type_node = $object->createValueNode('objectValidationStatus', ['E55', 'objectValidationStatus'], $status);

        return $object_node->relateTo($type_node, 'P2')->save();
    }
}

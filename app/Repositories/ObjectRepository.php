<?php

namespace App\Repositories;

use App\Models\Object;
use App\Models\ProductionClassification;
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
     * @param $objectId             integer The id of the object
     * @param $classification array   The configuration of the classification
     *
     * @return Node
     */
    public function addClassification($objectId, $classification)
    {
        $object = $this->getById($objectId);

        if (! empty($object)) {
            $query = "MATCH (object:E22)-[P108]->(productionEvent:productionEvent)
            WHERE id(object) = $objectId return productionEvent, object";

            $client = $this->getClient();

            $cypherQuery = new Query($client, $query);
            $results = $cypherQuery->getResultSet();

            $prodClassification = new ProductionClassification($classification);
            $prodClassification->save();

            if ($results->count() > 0) {
                $row = $results->current();
                $production_event = $row['productionEvent'];

                $production_event->relateTo($prodClassification->getNode(), 'P41')->save();
            } else {
                $production_event = new ProductionEvent(['productionClassification' => $classification]);

                $object->relateTo($production_event, 'P108')->save();
            }

            return $object;
        }

        return null;
    }

    public function getClassification($objectId, $classification_id)
    {
        // To make this more neat, we'll use a specific Cypher query to bypass the
        // productionEvent link that lies between object and classification
        $query = "match (n)-[*2..2]-(classification) where id(n) = $objectId AND id(classification) = $classification_id return classification";

        $client = $this->getClient();

        $cypherQuery = new Query($client, $query);
        $result = $cypherQuery->getResultSet();

        if (! empty($result->current())) {
            return $result->current()->current();
        } else {
            return null;
        }
    }

    /**
     * Get the related user id for a given object
     *
     * @param integer $objectId The id of the object
     *
     * @return integer
     */
    public function getRelatedUserId($objectId)
    {
        $queryString = "MATCH (n:object)-[P12]-(find:findEvent)-[P29]-(person:person)
                WHERE id(n) = $objectId
                return person";

        $query = new Query($this->getClient(), $queryString);

        $results = $query->getResultSet();

        if ($results->count() > 0 && ! empty($results->current())) {
            $person = $results->current()->current();

            return $person->getId();
        } else {
            return null;
        }
    }

    /**
     * Get the related findEvent id for a given object
     *
     * @param integer $objectId The id of the object
     *
     * @return integer
     */
    public function getRelatedFindEventId($objectId)
    {
        $queryString = "MATCH (n:object)-[P12]-(find:findEvent)
                WHERE id(n) = $objectId
                return find";

        $query = new Query($this->getClient(), $queryString);

        $results = $query->getResultSet();

        if (! empty($results->current())) {
            $find = $results->current()->current();

            return $find->getId();
        } else {
            return null;
        }
    }

    /**
     * Set the validation status of an object
     *
     * @param integer $objectId The id of the object
     * @param string  $status   The new status of the object
     * @param array   $feedback The given feedback on different properties
     * @param boolean $embargo
     *
     * @return Node
     */
    public function setValidationStatus($objectId, $status, $feedback, $embargo)
    {
        $objectNode = $this->getById($objectId);

        $relationships = $objectNode->getRelationships(['P2'], Relationship::DirectionOut);

        foreach ($relationships as $relationship) {
            $typeNode = $relationship->getEndNode();

            if ($typeNode->getProperty('name') == 'objectValidationStatus') {
                $relationship->delete();
                $typeNode->delete();

                break;
            }
        }

        $object = new Object();
        $object->setNode($objectNode);

        $typeNode = $object->createValueNode('objectValidationStatus', ['E55', 'objectValidationStatus'], $status);

        if (! empty($feedback)) {
            // Append the feedback if feedback already exists
            $currentFeedback = $objectNode->getProperty('feedback');

            if (! empty($currentFeedback)) {
                $currentFeedback = json_decode($currentFeedback, true);
            } else {
                $currentFeedback = [];
            }

            $currentFeedback[] = $feedback;

            $objectNode->setProperty('feedback', json_encode($currentFeedback))->save();
        }

        // Set the embargo property
        $objectNode->setProperty('embargo', $embargo)->save();

        return $objectNode->relateTo($typeNode, 'P2')->save();
    }
}

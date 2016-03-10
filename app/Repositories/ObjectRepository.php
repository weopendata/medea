<?php

namespace App\Repositories;

use App\Models\Object;
use App\Models\ProductionEvent;

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
}

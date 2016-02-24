<?php

namespace App\Repositories;

use App\Models\Object;

class ObjectRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Object::$NODE_TYPE, Object::class);
    }

    public function store($properties = [])
    {
        // Create and save a new object
        $object = Object::create($properties);

        $object->save();

        // Create the dimensions and relate them
        foreach ($properties['dimensions'] as $dimension) {
            $dimension_node = $object->createDimension($dimension);

            $object->getNode()->relateTo($dimension_node, 'HAS_DIMENSION');
        }

        // Create the classifications of the object

        // Relate the classifications with the object

        return $object;
    }
}

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
        $object = new Object($properties);

        $object->save();

        return $object;
    }
}

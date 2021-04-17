<?php


namespace App\Repositories;


use App\Models\ExcavationEvent;

class ExcavationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(ExcavationEvent::$NODE_TYPE, ExcavationEvent::class);
    }

    /**
     * @param array $properties
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function store(array $properties)
    {
        $excavation = new ExcavationEvent($properties);

        $excavation->save();

        return $excavation->getId();
    }
}

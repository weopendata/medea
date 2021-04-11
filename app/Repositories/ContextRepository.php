<?php


namespace App\Repositories;


use App\Models\Context;
use App\Repositories\BaseRepository;

class ContextRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('S22', Context::class);
    }

    /**
     * @param array $properties
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function store(array $properties)
    {
        $excavation = new Context($properties);

        $excavation->save();

        return $excavation->getId();
    }
}

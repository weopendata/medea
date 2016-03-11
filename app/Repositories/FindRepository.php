<?php

namespace App\Repositories;

use App\Models\FindEvent;
use App\Models\ProductionClassification;

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

    public function get($limit, $offset)
    {
        $client = $this->getClient();

        $finds = [];

        $find_label = $client->makeLabel($this->label);

        $find_nodes = $find_label->getNodes();

        foreach ($find_nodes as $find_node) {
            // Build a structure out of a find event
            $finds[] = $this->expandValues($find_node->getId());
        }

        return $finds;
    }
}

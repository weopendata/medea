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
            $find_model = new $this->model();
            $find_model->setNode($find_node);
            $finds[] = $this->buildFindEvent($find_model);
        }

        return $finds;
    }

    /**
     * Fetches the relevant data that the front-end needs
     * in order to visualize a certain find and it's related data
     *
     * @param Node $find
     *
     * @return array
     */
    private function buildFindEvent($find)
    {
        return $find->getValues();
    }
}

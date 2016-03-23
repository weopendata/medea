<?php

namespace App\Repositories;

use App\Models\FindEvent;
use App\Models\ProductionClassification;
use Everyman\Neo4j\Relationship;

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

    /**
     * Get all the bare nodes of a find
     *
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function getAll($limit = 500, $offset = 0)
    {
        $client = $this->getClient();

        $finds = [];

        $find_label = $client->makeLabel($this->label);

        // TODO paging (not sure if that's even possible in the regular RMDBS sense)
        $find_nodes = $find_label->getNodes();

        return $find_nodes;
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
}

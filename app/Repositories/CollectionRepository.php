<?php

namespace App\Repositories;

use App\Models\Collection;
use Everyman\Neo4j\Cypher\Query;

class CollectionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Collection::$NODE_TYPE, Collection::class);
    }

    /**
     * Create a new collection
     *
     * @param  array $properties
     * @return Node
     */
    public function store($properties)
    {
        $collection = new Collection($properties);
        $collection->save();

        return $collection;
    }

    public function getByTitle($title)
    {
        $title = trim($title);

        $queryString = 'MATCH (n:collection)
        WHERE n.title =~ {title}
        RETURN n';

        $query = new Query($this->getClient(), $queryString, ['title' => $title]);

        $results = $query->getResultSet();

        if (empty($results)) {
            return [];
        }

        return $results[0]->current();
    }
}

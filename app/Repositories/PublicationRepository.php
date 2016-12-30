<?php

namespace App\Repositories;

use Everyman\Neo4j\Cypher\Query;
use App\Models\Publication;

class PublicationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Publication::$NODE_TYPE, Publication::class);
    }

    /**
     * Search for publications with a matching title
     *
     * @param  string $searchString
     * @return array
     */
    public function search($searchString)
    {
        $queryString = '
        MATCH (publication:E31)-[P102]->(title:E35)
        WHERE title.value =~{searchString}
        RETURN publication, title.value as title
        LIMIT 20';

        $variables = [
            'searchString' => '.*' . $searchString . '.*'
        ];

        $cypherQuery = new Query($this->getClient(), $queryString, $variables);

        $results = [];

        foreach ($cypherQuery->getResultSet() as $row) {
            $results[] = [
                'identifier' => $row['publication']->getId(),
                'title' => $row['title']
            ];
        }

        return $results;
    }
}

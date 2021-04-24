<?php

namespace App\Repositories;

use App\Services\NodeService;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;


/**
 * Class SuggestionRepository
 * @package App\Repositories
 */
class SuggestionRepository
{
    /**
     * Search for suggestions for a specific type based on a search string
     *
     * @param string $facet The facet of the publication to search for: title, creationActorName
     * @param string $searchString
     * @return array
     * @throws \Exception
     */
    public function suggest($facet, $searchString)
    {
        if (! in_array($facet, ['title', 'author'])) {
            return [];
        }

        // Limit the search string
        $searchString = str_limit($searchString, 100, '');

        if ($facet == 'title') {
            $tenantStatement = NodeService::getTenantWhereStatement(['publication', 'title']);

            $queryString = "
                MATCH (publication:E31)-[r:P102]->(title:E35)
                WHERE title.value =~ {searchString} AND $tenantStatement
                RETURN distinct title.value as result
                LIMIT 150";
        } else if ($facet == 'author') {
            $tenantStatement = NodeService::getTenantWhereStatement(['publication', 'authorName']);

            $queryString = '
                MATCH (publication:E31)-[r:P94]->(E64)-[r:P14]->(E39)-[r:P131]->(authorName:E82)
                WHERE authorName.value =~ {searchString} AND authorName.name="publicationCreationActorName" AND ' . $tenantStatement .
                ' RETURN distinct authorName.value as result
                LIMIT 150';
        }

        $variables = [
            'searchString' => '(?i).*' . $searchString . '.*'
        ];

        $cypherQuery = new Query($this->getClient(), $queryString, $variables);

        $results = [];

        foreach ($cypherQuery->getResultSet() as $row) {
            $results[] = $row['result'];
        }

        return collect($results)->unique()->values()->toArray();
    }

    protected function getClient()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create a new client with user and password
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        return $client;
    }
}

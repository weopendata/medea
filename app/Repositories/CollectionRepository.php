<?php

namespace App\Repositories;

use App\Models\Collection;
use App\Services\NodeService;
use Everyman\Neo4j\Cypher\Query;

/**
 * Class CollectionRepository
 * @package App\Repositories
 */
class CollectionRepository extends BaseRepository
{
    /**
     * CollectionRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(Collection::$NODE_TYPE, Collection::class);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function countAllCollections()
    {
        $client = $this->getClient();

        $tenantStatement = NodeService::getTenantWhereStatement(['n']);

        $queryString = "MATCH (n:collection)
        WHERE $tenantStatement
        RETURN count(distinct n)";

        $cypherQuery = new Query($client, $queryString);
        $results = $cypherQuery->getResultSet();

        return $results->current()->current();
    }

    /**
     * Return a simple list of the all collections
     * This list is a mapping between id's and the title of the collection
     *
     * @return array
     * @throws \Exception
     */
    public function getList()
    {
        $variables = [];

        $tenantStatement = NodeService::getTenantWhereStatement(['n']);

        $queryString = "MATCH (n:collection)
        WHERE $tenantStatement
        RETURN n as collection";

        $client = $this->getClient();

        $cypherQuery = new Query($client, $queryString, $variables);
        $results = $cypherQuery->getResultSet();

        $collections = [];

        foreach ($results as $result) {
            $collections[$result['collection']->getId()] = $result['collection']->getProperty('title');
        }

        return $collections;
    }

    /**
     * Get all the collection nodes
     *
     * @param integer $limit
     * @param integer $offset
     * @param string $sortBy The field to sort by (title|created_at)
     * @param string $sortOrder The sort order (ASC|DESC)
     *
     * @return array
     * @throws \Exception
     */
    public function getAll($limit = 50, $offset = 0, $sortBy = null, $sortOrder = 'DESC')
    {
        $client = $this->getClient();

        $variables = [];

        $tenantStatement = NodeService::getTenantWhereStatement(['n']);

        $queryString = "MATCH (n:collection)
        OPTIONAL MATCH (n)-[p1:P109]->(person:person)
        OPTIONAL MATCH (n)-[p2:P109]->(institution:E40)-[P131]->(institutionAppellation:E82)
        WHERE $tenantStatement
        RETURN n as collection, n.title as collectionTitle, collect(distinct person) as person, collect(institutionAppellation) as instNames";

        if (! empty($sortBy)) {
            // Statements in functions don't seem to work with the Jadell Neo4J library
            if ($sortBy == 'title') {
                $orderBy = 'LOWER(n.title)';
            } else {
                $orderBy = 'n.created_at';
            }

            // Don't allow injection,
            // for some reason statement binding
            // the order statement doesn't work
            if ($sortOrder == 'ASC') {
                $sortOrder = 'ASC';
            } else {
                $sortOrder = 'DESC';
            }

            $queryString .= ' ORDER BY ' . $orderBy . ' ' . $sortOrder;
        }

        $queryString .= ' SKIP {offset} LIMIT {limit}';

        $variables['offset'] = (int) $offset;
        $variables['limit'] = (int) $limit;

        $cypherQuery = new Query($client, $queryString, $variables);
        $results = $cypherQuery->getResultSet();

        $collections = [];

        foreach ($results as $result) {
            $users = [];

            foreach ($result['person'] as $person) {
                $users[] = [
                    'identifier' => $person->getId(),
                    'firstName' => $person->getProperty('firstName'),
                    'lastName' => $person->getProperty('lastName'),
                    'email' => $person->getProperty('email'),
                ];
            }

            $institutions = [];

            foreach ($result['instNames'] as $institution) {
                $institutions[] = [
                    'institutionAppellation' => $institution->getProperty('value'),
                ];
            }

            $collections[] = [
                'title' => $result['collection']->getProperty('title'),
                'collectionType' => $result['collection']->getProperty('collectionType'),
                'identifier' => $result['collection']->getId(),
                'description' => $result['collection']->getProperty('description'),
                'persons' => $users,
                'institution' => $institutions,
                'created_at' => $result['collection']->getProperty('created_at'),
                'collectionType' => $result['collection']->getProperty('collectionType'),
            ];
        }

        return $collections;
    }

    /**
     * Link a user (=maintainer ) to a collection
     *
     * @param integer $collectionId
     * @param integer $userId
     * @return boolean
     * @throws \Everyman\Neo4j\Exception
     */
    public function linkUser($collectionId, $userId)
    {
        // Check if there's already a link
        if ($this->isCollectionLinkedWithUser($collectionId, $userId)) {
            return false;
        }

        // Get the collection
        $collection = $this->getById($collectionId);

        // Get the user
        $user = app(UserRepository::class)->getById($userId);

        if (empty($user) || empty($collection)) {
            return false;
        }

        // Link the user and the collection
        $collection->relateTo($user, 'P109')->save();

        return true;
    }

    /**
     * Unlink a user (=maintainer ) of a collection
     *
     * @param  int     $collectionId
     * @param  int     $userId
     * @return boolean
     */
    public function unlinkUser($collectionId, $userId)
    {
        $collection = $this->getById($collectionId);

        if (empty($collection)) {
            return false;
        }

        $relationships = $collection->getRelationships(['P109']);

        foreach ($relationships as $relationship) {
            if ($relationship->getEndNode()->getId() == $userId) {
                $relationship->delete();
            }
        }

        return true;
    }

    /**
     * Check if the user and collection are linked with each other
     *
     * @param integer $collectionId
     * @param integer $userId
     * @return boolean
     * @throws \Exception
     */
    private function isCollectionLinkedWithUser($collectionId, $userId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['n', 'p']);

        $query = "MATCH (n:collection)-[P109]->(p:person)
        WHERE id(p) = {userId} AND id(n) = {collectionId} AND $tenantStatement
        RETURN n";

        $variables = [
            'userId' => (int) $userId,
            'collectionId' => (int) $collectionId
        ];

        $query = new Query($this->getClient(), $query, $variables);
        $results = $query->getResultSet();

        return $results->count() > 0;
    }

    /**
     * Create a new collection
     *
     * @param array $properties
     * @return Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function store($properties)
    {
        $collection = new Collection($properties);
        $collection->save();

        return $collection;
    }

    /**
     * Search for a collection by its title or a piece of the title
     *
     * @param string $queryString
     * @return array
     * @throws \Exception
     */
    public function search($queryString = '')
    {
        if (empty($queryString)) {
            return $this->getAll();
        }

        $tenantStatement = NodeService::getTenantWhereStatement('n');

        $query = "MATCH (n:E78)
        WHERE n.title =~ {queryString} AND $tenantStatement
        RETURN n";

        $variables = ['queryString' =>  '(?i).*' . $queryString . '.*'];

        $query = new Query($this->getClient(), $query, $variables);
        $results = $query->getResultSet();

        $collections = [];

        foreach ($results as $result) {
            $result = $result->current();

            $collections[] = [
                'identifier' => $result->getId(),
                'title' => $result->getProperty('title'),
            ];
        }

        return $collections;
    }

    /**
     * Get the users linked to the collection
     *
     * @param int $collectionId
     * @return array
     * @throws \Everyman\Neo4j\Exception
     */
    public function getLinkedUsers($collectionId)
    {
        $collection = $this->getById($collectionId);

        $users = [];

        if (empty($collection)) {
            return $users;
        }

        $relationships = $collection->getRelationships(['P109']);

        foreach ($relationships as $relationship) {
            $endNode = $relationship->getEndNode();

            foreach ($endNode->getLabels() as $label) {
                if ($label->getName() == 'person') {
                    $userNode = $relationship->getEndNode();

                    $users[] = [
                        'identifier' => $userNode->getId(),
                        'firstName' => $userNode->getProperty('firstName'),
                        'lastName' => $userNode->getProperty('lastName'),
                        'email' => $userNode->getProperty('email'),
                    ];
                }
            }
        }

        return $users;
    }

    /**
     * Get the collection that is linked to an object
     *
     * @param int $objectId
     * @return array
     * @throws \Exception
     */
    public function getCollectionForObject($objectId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['n', 'collection']);

        $query = "MATCH (n:object)-[P24]->(collection:collection)
        WHERE id(n) = {objectId} AND $tenantStatement
        RETURN collection";

        $variables = ['objectId' => $objectId];

        $query = new Query($this->getClient(), $query, $variables);
        $results = $query->getResultSet();

        $collection = [];

        if ($results->count() > 0) {
            $collectionNode = $results->current()['collection'];

            $collection = [
                'identifier' => $collectionNode->getId(),
                'title' => $collectionNode->getProperty('title'),
            ];
        }

        return $collection;
    }

    /**
     * Get the collections for a user
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getForUser($userId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['n', 'person']);

        $query = "MATCH (n:collection)-[P109]->(person:person)
        WHERE id(person) = {userId} AND $tenantStatement
        RETURN distinct n";

        $variables = ['userId' => $userId];

        $query = new Query($this->getClient(), $query, $variables);
        $results = $query->getResultSet();

        $collections = [];

        foreach ($results as $result) {
            $result = $result->current();

            $collections[] = [
                'identifier' => $result->getId(),
                'title' => $result->getProperty('title'),
            ];
        }

        return $collections;
    }

    public function getByTitle($title)
    {
        $title = trim($title);

        $tenantStatement = NodeService::getTenantWhereStatement(['n']);

        $queryString = "MATCH (n:collection)
        WHERE n.title =~ {title} AND $tenantStatement
        RETURN n";

        $query = new Query($this->getClient(), $queryString, ['title' => $title]);

        $results = $query->getResultSet();

        if ($results->count() < 1) {
            return [];
        }

        return $results[0]->current();
    }
}

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

    public function countAllCollections()
    {
        $client = $this->getClient();

        $queryString = 'MATCH (n:collection)
        RETURN count(distinct n)';

        $cypherQuery = new Query($client, $queryString);
        $results = $cypherQuery->getResultSet();

        return $results->current()->current();
    }

    /**
     * Get all the collection nodes
     *
     * @param integer $limit
     * @param integer $offset
     * @param string  $sortBy    The field to sort by (title|created_at)
     * @param string  $sortOrder The sort order (ASC|DESC)
     *
     * @return array
     */
    public function getAll($limit = 50, $offset = 0, $sortBy = null, $sortOrder = 'DESC')
    {
        $client = $this->getClient();

        $variables = [];

        $queryString = 'MATCH (n:collection)
        OPTIONAL MATCH (n:collection)-[P109]->(person:person)
        RETURN n as collection, n.title as collectionTitle, collect(person) as person';

        if (! empty($sortBy)) {
            // Statements in functions don't seem to work with the jadell library
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
            $collectionNode = $result->current();

            $users = [];

            foreach ($result['person'] as $person) {
                $users[] = [
                    'identifier' => $person->getId(),
                    'firstName' => $person->getProperty('firstName'),
                    'lastName' => $person->getProperty('lastName'),
                    'email' => $person->getProperty('email'),
                ];
            }

            $collections[] = [
                'title' => $result['collection']->getProperty('title'),
                'collectionType' => $result['collection']->getProperty('collectionType'),
                'identifier' => $result['collection']->getId(),
                'description' => $result['collection']->getProperty('description'),
                'persons' => $users
            ];
        }

        return $collections;
    }

    /**
     * Link a user (=maintainer ) to a collection
     *
     * @param  integer $collectionId
     * @param  integer $userId
     * @return boolean
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
     * Check if the user and collection are linked with eachother
     *
     * @param  integer $collectionId
     * @param  integer $userId
     * @return boolean
     */
    private function isCollectionLinkedWithUser($collectionId, $userId)
    {
        $query = 'MATCH (n:collection)-[P109]->(p:person)
        WHERE id(p) = {userId} AND id(n) = {collectionId}
        RETURN n';

        $variables = [
            'userId' => (int) $userId,
            'collectionId' => (int) $collectionId
        ];

        $query = new Query($this->getClient(), $query, $variables);
        $results = $query->getResultSet();

        return $results->count() > 0;
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

    /**
     * Search for a collection by its title or a piece of the title
     *
     * @param  string $queryString
     * @return array
     */
    public function search($queryString = '')
    {
        if (empty($queryString)) {
            return $this->getAll();
        }

        $query = 'MATCH (n:E78)
        WHERE n.title =~ {queryString}
        RETURN n';

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
     * @param  int   $collectionId
     * @return array
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
     * Get the collections for a user
     *
     * @param  int   $userId
     * @return array
     */
    public function getForUser($userId)
    {
        $query = 'MATCH (n:collection)-[P109]->(person:person)
        WHERE id(person) = {userId}
        RETURN distinct n';

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

        $queryString = 'MATCH (n:collection)
        WHERE n.title =~ {title}
        RETURN n';

        $query = new Query($this->getClient(), $queryString, ['title' => $title]);

        $results = $query->getResultSet();

        if ($results->count() < 1) {
            return [];
        }

        return $results[0]->current();
    }
}

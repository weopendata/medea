<?php

namespace App\Repositories;

use App\Models\Person;
use Everyman\Neo4j\Cypher\Query;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('E21', Person::class);
    }

    /**
     * Create a new user
     *
     * @param array $properties
     *
     * @return Node
     */
    public function store($properties)
    {
        $person = new Person($properties);
        $person->save();

        return $person;
    }

    /**
     * Get a user based on an email
     *
     * @param string $email
     *
     * @return Node
     */
    public function getUser($email)
    {
        // Label (= type) is already configured for Person
        $label = $this->getLabel();

        $user_nodes = $label->getNodes('email', $email);

        if ($user_nodes->count() > 0) {
            return $user_nodes->current();
        } else {
            return [];
        }
    }

    /**
     * Get a user based on the token and email
     *
     * @param string $token
     *
     * @return Node
     */
    public function getByPasswordResetToken($token, $email)
    {
        $users = $this->getLabel()->getNodes('email', $email);

        if ($users->count() > 0) {
            $user = $users->current();

            $person = new Person();

            if ($user->getProperty($person->getPasswordResetTokenName()) != $token) {
                return null;
            }

            return $user;
        }

        return null;
    }

    /**
     * Check if a user exists
     *
     * @param string $email
     *
     * @return boolean
     */
    public function userExists($email)
    {
        // Label (= type) is already configured for Person
        $label = $this->getLabel();

        // Get all of the Person node with the admin email
        return $label->getNodes('email', $email)->count() > 0;
    }

    /**
     * Verify a user with a certain token
     *
     * @param string $token
     *
     * @return Node
     */
    public function confirmUser($token)
    {
        // Label (= type) is already configured for Person
        $label = $this->getLabel();

        if ($label->getNodes('token', $token)->count() > 0) {
            $user = $label->getNodes('token', $token)->current();

            if (! empty($user)) {
                $user->setProperty('verified', true);
                $user->setProperty('token', '');
                $user->save();

                return $user;
            }
        }

        return null;
    }

    /**
     * Deny and delete a user with a certain token
     *
     * @param string $token
     *
     * @return Node
     */
    public function denyUser($token)
    {
        // Label (= type) is already configured for Person
        $label = $this->getLabel();

        if ($label->getNodes('token', $token)->count() > 0) {
            $user = $label->getNodes('token', $token)->current();

            if (! empty($user)) {
                $person = new Person();
                $person->setNode($user);
                $person->delete();

                return $user;
            }
        }

        return null;
    }

    /**
     * Make a vote connection between a user and a classification
     *
     * @param Node    $classification
     * @param integer $personId
     * @param string  $vote_type      agree|disagree
     *
     * @return Relationship
     */
    public function addVote($classification, $personId, $vote_type)
    {
        $user_node = $this->getById($personId);

        return $user_node->relateTo($classification, $vote_type)->save();
    }

    /**
     * Get all the user nodes
     *
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function getAll($limit = 20, $offset = 0)
    {
        $client = $this->getClient();

        $personLabel = $client->makeLabel($this->label);

        return $personLabel->getNodes();
    }

    /**
     * Get all users with only a specific set of data points
     *
     * @param array   $fields
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function getAllWithFields($fields, $limit = 20, $offset = 0)
    {
        $userNodes = $this->getAll($limit, $offset);

        $users = [];

        foreach ($userNodes as $userNode) {
            $person = new Person();
            $person->setNode($this->getById($userNode->getId()));

            $personData = array_only($userNode->getProperties(), $fields);

            // Don't add the default administrator to the list

            $personData['id'] = $userNode->getId();
            $personData['finds'] = $person->getFindCount();
            $personData['hasPublicProfile'] = $person->hasPublicProfile();

            $users[] = $personData;
        }

        return $users;
    }

    /**
     * Get all the bare nodes of a findEvent
     *
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function getAllWithRoles()
    {
        $client = $this->getClient();

        $findLabel = $client->makeLabel($this->label);

        $findNodes = $findLabel->getNodes();

        $data = [];
        foreach ($findNodes as $findNode) {
            $person = new Person();
            $person->setNode($findNode);
            $personData = array_only($findNode->getProperties(), ['firstName', 'lastName', 'verified']);
            $personData['id'] = $findNode->getId();
            $personData['personType'] = $person->getRoles();

            $data[] = $personData;
        }
        return $data;
    }

    /**
     * Get all users with saved searches
     *
     * @return array
     */
    public function getAllWithSavedSearches()
    {
        $query = 'MATCH (n:person) where has (n.savedSearches) return n';

        $cypherQuery = new Query($this->getClient(), $query);

        $users = [];

        foreach ($cypherQuery->getResultSet() as $row) {
            $result = $row['n'];

            $users[] = [
                'user_id' => $result->getId(),
                'searches' => json_decode($result->savedSearches, true)
            ];
        }

        return $users;
    }

    /**
     * Get the find count for a user
     *
     * @param integer $userId
     *
     * @return array
     */
    public function getFindCountForUser($userId)
    {
        $query = "MATCH (n:findEvent)-[P29]->(p:person) where id(p)= $userId return count(n)";

        $cypherQuery = new Query($this->getClient(), $query);
        $resultSet = $cypherQuery->getResultSet();

        if ($resultSet->count() > 0) {
            return $resultSet->current()[0];
        }

        return 0;
    }
}

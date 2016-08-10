<?php

namespace App\Repositories;

use App\Models\Person;

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
        $client = $this->getClient();

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

        // Get all of the Person node with the admin email
        $user_nodes = $label->getNodes("email", $email);

        if ($user_nodes->count() > 0) {
            return $user_nodes->current();
        } else {
            return [];
        }
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
        return $label->getNodes("email", $email)->count() > 0;
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

            if (!empty($user)) {
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

            if (!empty($user)) {
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
     * @param integer $person_id
     * @param string  $vote_type agree|disagree
     *
     * @return Relationship
     */
    public function addVote($classification, $person_id, $vote_type)
    {
        $user_node = $this->getById();

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
    public function getAll($limit = 50, $offset = 0)
    {
        $client = $this->getClient();

        $personLabel = $client->makeLabel($this->label);

        return $personLabel->getNodes();
    }

    /**
     * Get all users with only a specific set of data points
     *
     * @param array $fields
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function getAllWithFields($fields, $limit = 50, $offset = 0)
    {
        $userNodes = $this->getAll($limit, $offset);

        $users = [];

        foreach ($userNodes as $userNode) {
            $person = new Person();
            $person->setNode($userNode);

            $personData = array_only($userNode->getProperties(), $fields);
            $personData['id'] = $userNode->getId();
            $personData['finds'] = $person->getFindCount();

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
}

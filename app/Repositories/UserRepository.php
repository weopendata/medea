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

            $user->setProperty('verified', true);
            $user->setProperty('token', '');
            $user->save();
        }
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
}

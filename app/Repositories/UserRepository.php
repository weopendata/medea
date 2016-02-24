<?php

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('Person');
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
        return $label->getNodes("email", $email)->current();
    }
}

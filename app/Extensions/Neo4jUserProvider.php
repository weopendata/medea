<?php

namespace App\Extensions;

use Illuminate\Contracts\Auth\UserProvider;
use App\Models\Person;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable;
use Everyman\Neo4j\Client;

/**
 * Class Neo4jUserProvider
 * @package App\Extensions
 *
 * TODO: add multi-tenancy
 */
class Neo4jUserProvider implements UserProvider
{
    public function __construct()
    {
        $this->person_label = $this->getPersonLabel();
    }

    private function getPersonLabel()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create an admin
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        // Set a label configured client, equivalent of only returning a certain eloquent model
        return $client->makeLabel('E21');
    }

     /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $users = $this->person_label->getNodes('email', $identifier);

        if ($users->count() > 0) {
            $person = new Person();
            $person->setNode($users[0]);

            return $person;
        }

        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed   $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $users = $this->person_label->getNodes('email', $identifier);

        if ($users->count() > 0) {
            $user = $users[0];

            if ($user->getProperty('remember_me') == $token) {
                $person = new Person();
                $person->setNode($users[0]);

                return $person;
            }
        }

        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $users = $this->person_label->getNodes('email', $user->email);

        if ($users->count() > 0) {
            $user_node = $users[0];

            $person = new Person();
            $person->setNode($user_node);
            $person->setRememberToken($token);
        }
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $users = $this->person_label->getNodes('email', $credentials['email']);

        if ($users->count() > 0) {
            $user_node = $users[0];

            $person = new Person();
            $person->setNode($user_node);

            return $person;
        }

        return user;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::make($credentials['password']) == $user->getAuthPassword();
    }
}

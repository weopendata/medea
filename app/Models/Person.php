<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Everyman\Neo4j\Relationship;

class Person extends Base implements Authenticatable
{
    public static $NODE_TYPE = 'E21';
    public static $NODE_NAME = 'Person';

    protected $has_unique_id = true;

    protected $implicitModels = [
        [
            'relationship' => 'P131',
            'config' => [
                'key' => 'firstName',
                'name' => 'firstName',
                'cidoc_type' => 'E82',
            ]
        ],
        [
            'relationship' => 'P131',
            'config' => [
                'key' => 'lastName',
                'name' => 'lastName',
                'cidoc_type' => 'E82',
            ]
        ],
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'personType',
                'name' => 'personType',
                'cidoc_type' => 'E55',
                'plural' => true
            ]
        ]
    ];

    protected $properties = [
        [
            'name' => 'email',
        ],
        [
            'name' => 'verified',
            'default_value' => 'false'
        ],
        [
            'name' => 'firstName'
        ],
        [
            'name' => 'lastName'
        ]
    ];

    public function __construct($properties = [])
    {
        parent::__construct($properties);

        if (!empty($properties)) {
            $this->node->setProperty('token', str_random(40));
            $this->node->setProperty('password', Hash::make($properties['password']));
            $this->node->save();
        }
    }

    public function createFirstName($first_name)
    {
        $name_node = $this->createValueNode('value', ['E82', 'personName'], $first_name);

        $name_type = $this->createValueNode('voornaam', ['E55', 'personNameType'], 'voornaam');

        $name_node->relateTo($name_type, 'P2')->save();

        return $name_node;
    }

    public function createLastName($last_name)
    {
        $name_node = $this->createValueNode('value', ['E82', 'personName'], $last_name);

        $name_type = $this->createValueNode('achternaam', ['E55', 'personNameType'], 'achternaam');

        $name_node->relateTo($name_type, 'P2')->save();

        return $name_node;
    }

    public function createPersonType($type)
    {
        $person_type = $this->createValueNode('personType', ['E55', 'personType'], $type);

        return $person_type;
    }

    /**
     * Set the Node object for the Person model
     *
     * @param Node $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        // Is always the same in Neo4j
        return 'email';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->node->getProperty($this->getAuthIdentifierName());
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->node->getProperty('password');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->node->getProperty($this->getRememberTokenName());
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->node->setProperty($this->getRememberTokenName(), $value);
        $this->node->save();
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the roles of a user
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = [];

        $type_rel = $this->node->getRelationships(['P2'], Relationship::DirectionOut);

        foreach ($type_rel as $rel) {
            $end_node = $rel->getEndNode();
            $labels = $end_node->getLabels();

            foreach ($labels as $label) {
                if ($label->getName() == 'personType') {
                    $roles[] = $end_node->getProperty('value');
                }
            }
        }

        return $roles;
    }

    public function __get($key)
    {
        return $this->node->getProperty($key);
    }
}

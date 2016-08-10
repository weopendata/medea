<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Everyman\Neo4j\Relationship;

class Person extends Base implements Authenticatable
{
    public static $NODE_TYPE = 'E21';
    public static $NODE_NAME = 'person';

    protected $hasUniqueId = true;

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
        ],
        [
            'relationship' => 'P76',
            'config' => [
                'key' => 'personContacts',
                'name' => 'personContacts',
                'cidoc_type' => 'E51',
                'plural' => true
            ]
        ],
        [
            'relationship' => 'P53',
            'config' => [
                'key' => 'personAddress',
                'name' => 'personAddress',
                'cidoc_type' => 'E53'
            ]
        ]
    ];

    protected $properties = [
        [
            'name' => 'email',
        ],
        [
            'name' => 'verified',
            'default_value' => false
        ],
        [
            'name' => 'firstName'
        ],
        [
            'name' => 'lastName'
        ],
        [
            'name' => 'profileAccessLevel'
        ],
        [
            'name' => 'showNameOnPublicFinds'
        ],
        [
            'name' => 'passContactInfoToAgency'
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
        $nameNode = $this->createValueNode('value', ['E82', 'personName'], $first_name);

        $nameType = $this->createValueNode('voornaam', ['E55', 'personNameType'], 'voornaam');

        $nameNode->relateTo($nameType, 'P2')->save();

        return $nameNode;
    }

    public function createLastName($last_name)
    {
        $nameNode = $this->createValueNode('value', ['E82', 'personName'], $last_name);

        $nameType = $this->createValueNode('achternaam', ['E55', 'personNameType'], 'achternaam');

        $nameNode->relateTo($nameType, 'P2')->save();

        return $nameNode;
    }

    public function createPersonType($type)
    {
        $personType = $this->createValueNode('personType', ['E55', 'personType'], $type);

        return $personType;
    }

    public function createPersonContacts($contact)
    {
        $contactsNode = $this->createValueNode('personContacts', ['E51', 'personContacts'], $contact);

        if (strripos($contact, '@')) {
            $contactType = $this->createValueNode('email', ['E55', 'personContactType'], 'email');
        } else {
            $contactType = $this->createValueNode('phone', ['E55', 'personContactType'], 'phone');
        }

        $contactsNode->relateTo($contactType, 'p2')->save();

        return $contactsNode;
    }

    public function createPersonAddress($address)
    {
        $addressProperties = [
            [
                'key' => 'street',
                'name' => 'personAddressStreet',
                'node_type' => 'E45'
            ],
            [
                'key' => 'number',
                'name' => 'personAddressNumber',
                'node_type' => 'E45'
            ],
            [
                'key' => 'postalCode',
                'name' => 'personAddressPostalCode',
                'node_type' => 'E45'
            ],
            [
                'key' => 'locality',
                'name' => 'personAddressLocality',
                'node_type' => 'E45'
            ]
        ];

        $client = $this->getClient();

        $addressNode = $client->makeNode();
        $addressNode->setProperty('name', 'address');
        $addressNode->save();

        $addressNode->addLabels([
            self::makeLabel('E53'), self::makeLabel('address'), self::makeLabel($this->getGeneralId())
        ]);

        foreach ($addressProperties as $addressProperty) {
            if (!empty($address[$addressProperty['key']])) {
                $node = $this->createValueNode(
                    $addressProperty['key'],
                    [$addressProperty['key'], $addressProperty['node_type']],
                    $address[$addressProperty['key']]
                );

                $addressNode->relateTo($node, 'P87')->save();
            }
        }

        return $addressNode;
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

    public function update($properties)
    {
        return $this->patch($properties);
    }

    /**
     * Perform a patch request, person is the
     * only model that requires this because not all
     * properties are passed in order to update the model
     * (e.g. password and other privacy related data points)
     *
     * @param array $properties The new properties of the person
     *
     * @return Node
     */
    public function patch($properties)
    {
        // Apply the new data properties we expect
        // a flat document with lastName, firstName, roles, ...
        $fullModel = $this->getValues();

        foreach ($properties as $key => $value) {
            $fullModel[$key] = $value;
        }

        unset($fullModel['_method']);
        unset($fullModel['id']);
        unset($fullModel['identifier']);

        // Invoke the update method
        return parent::update($fullModel);
    }

    /**
     * Check if a user has a certain role
     *
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
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
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getFindCount()
    {
        return rand(0, 20);
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
        if ($key == 'id') {
            return $this->node->getId();
        }

        return $this->node->getProperty($key);
    }
}

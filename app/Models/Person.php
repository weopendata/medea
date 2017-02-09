<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Everyman\Neo4j\Relationship;
use App\Repositories\UserRepository;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
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
            'name' => 'phone'
        ],
        [
            'name' => 'profileAccessLevel',
            'default_value' => 0
        ],
        [
            'name' => 'showContactForm',
            'default_value' => true
        ],
        [
            'name' => 'showEmail',
            'default_value' => false
        ],
        [
            'name' => 'showNameOnPublicFinds',
            'default_value' => false
        ],
        [
            'name' => 'passContactInfoToAgency',
            'default_value' => false,
        ],
        [
            'name' => 'function'
        ],
        [
            'name' => 'affiliation'
        ],
        [
            'name' => 'bio'
        ],
        [
            'name' => 'expertise'
        ],
        [
            'name' => 'research'
        ],
        [
            'name' => 'detectoristNumber'
        ],
        [
            'name' => 'savedSearches'
        ]
    ];

    private $fillable = [
        'personType',
        'email',
        'lastName',
        'firstName',
        'phone',
        'showContactForm',
        'showEmail',
        'function',
        'affiliation',
        'bio',
        'research',
        'expertise',
        'detectoristNumber',
        'savedSearches',
    ];

    public function __construct($properties = [])
    {
        if (! empty($properties)) {
            parent::__construct($properties);

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
                'key' => 'personAddressStreet',
                'name' => 'personAddressStreet',
                'node_type' => 'E45'
            ],
            [
                'key' => 'personAddressNumber',
                'name' => 'personAddressNumber',
                'node_type' => 'E45'
            ],
            [
                'key' => 'personAddressPostalCode',
                'name' => 'personAddressPostalCode',
                'node_type' => 'E45'
            ],
            [
                'key' => 'personAddressLocality',
                'name' => 'personAddressLocality',
                'node_type' => 'E45'
            ]
        ];

        $client = $this->getClient();

        $addressNode = $client->makeNode();
        $addressNode->setProperty('name', 'personAddress');
        $addressNode->save();

        $addressNode->addLabels([
            self::makeLabel('E53'), self::makeLabel('personAddress'), self::makeLabel($this->getGeneralId())
        ]);

        foreach ($addressProperties as $addressProperty) {
            if (! empty($address[$addressProperty['key']])) {
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
     * @param  string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->node->setProperty($this->getRememberTokenName(), $value);
        $this->node->save();
    }

    /**
     * Set the password reset token
     */
    public function setPasswordResetToken($token)
    {
        $this->node->setProperty($this->getPasswordResetTokenName(), $token)->save();
    }

    /**
     * Get the password reset token.
     *
     * @return string
     */
    public function getPasswordResetToken()
    {
        return $this->node->getProperty($this->getPasswordResetTokenName());
    }

    /**
     * Set the password of the user.
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword($password)
    {
        $password = Hash::make($password);

        $this->node->setProperty('password', $password)->save();
    }

    public function update($properties)
    {
        return $this->patch($properties);
    }

    /**
     * Perform a patch request
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
     * @param array|string $roles
     *
     * @return boolean
     */
    public function hasRole($roles)
    {
        if (! is_array($roles)) {
            $roles = [$roles];
        }

        return count(array_intersect($roles, $this->getRoles())) > 0;
    }

    /**
     * Get the property name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the property name for the password reset token.
     *
     * @return string
     */
    public function getPasswordResetTokenName()
    {
        return 'password_token';
    }

    /**
     * Get the public profile of a person
     * @TODO
     *
     * @return array
     */
    public function getPublicProfile()
    {
        $person = [];
        $person['created_at'] = substr($this->created_at, 0, 10);

        // Iterate over the default fillable fields
        foreach ($this->fillable as $property) {
            $person[$property] = $this->$property;
        }

        return $person;
    }

    /**
     * Indicates if the user has a public profile
     *
     * @return boolean
     */
    public function hasPublicProfile()
    {
        return is_numeric($this->profileAccessLevel) && $this->profileAccessLevel == 4;
    }

    /**
     * Indicates which roles are allowed to see the profile of this user
     *
     * @return array
     */
    public function getProfileAllowedRoles()
    {
        switch ($this->profileAccessLevel) {
            case 0:
                return ['onderzoeker', 'administrator'];
            case 3:
                return [
                    'administrator',
                    'agentschap',
                    'detectorist',
                    'onderzoeker',
                    'validator',
                    'vondstexpert',
                ];
        }

        return ['administrator'];
    }

    public function getSavedSearches()
    {
        $searches = $this->node->getProperty('savedSearches');

        if (! is_null($searches)) {
            return [];
        }

        return $searches;
    }

    public function isContactable()
    {
        return $this->showContactForm == true;
    }

    /**
     * Get the amount of finds for the person
     * Note: when a user has a lot of finds, it's
     * better to perform the count through a query
     *
     * @return string
     */
    public function getFindCount()
    {
        $users = new UserRepository();

        return $users->getFindCountForUser($this->id);
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

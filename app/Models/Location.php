<?php

namespace App\Models;

class Location extends Base
{
    public static $NODE_TYPE = 'E53';
    public static $NODE_NAME = 'location';

    protected $implicitModels = [
        [
            'relationship' => 'P87',
            'config' => [
                'key' => 'locationPlaceName',
                'name' => 'locationPlaceName'
            ]
        ],
        [
            'relationship' => 'P87',
            'config' => [
                'key' => 'coordinates',
                'name' => 'coordinates',
                'value_node' => true,
                'cidoc_type' => 'E47'
            ]
        ],
        [
            'relationship' => 'P89',
            'config' => [
                'key' => 'address',
                'name' => 'address',
                'required' => false
            ]
        ],
    ];

    protected $relatedModels = [
    ];

    public function save()
    {
        parent::save();

        // Add an ID to the node
        $client = self::getClient();
        $id_node = $this->createValueNode('identifier', ['E42', 'locationplaceNameId'], $this->node->getId());

        $this->node->relateTo($id_node, 'P1')->save();
    }

    public function createAddress($address)
    {
        $address_properties = [
            [
                'key' => 'street',
                'name' => 'locationAddressStreet',
                'node_type' => 'E45'
            ],
            [
                'key' => 'number',
                'name' => 'locationAddressNumber',
                'node_type' => 'E45'
            ],
            [
                'key' => 'postalCode',
                'name' => 'locationAddressPostalCode',
                'node_type' => 'E45'
            ],
            [
                'key' => 'locality',
                'name' => 'locationAddressLocality',
                'node_type' => 'E45'
            ]
        ];

        $client = $this->getClient();

        $address_node = $client->makeNode();
        $address_node->setProperty('name', 'address');
        $address_node->save();

        $address_node->addLabels([
            self::makeLabel('E53'), self::makeLabel('address'), self::makeLabel($this->getGeneralId())
        ]);

        foreach ($address_properties as $address_property) {
            if (!empty($address[$address_property['key']])) {
                $node = $this->createValueNode(
                    $address_property['key'],
                    [$address_property['key'], $address_property['node_type']],
                    $address[$address_property['key']]
                );

                $address_node->relateTo($node, 'P87')->save();
            }
        }


        return $address_node;
    }

    public function createLocationPlaceName($spatial)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        // Make E48 Place name
        $place_name_node = $client->makeNode();
        $place_name_node->setProperty('name', 'locationPlaceName');
        $place_name_node->save();

        $place_name_node->addLabels([self::makeLabel('E48'), self::makeLabel('locationplaceName')]);

        // Combine E48 with it's explicit ID -> E42 identifier
        $id_node = $this->createValueNode('identifier', ['E42', 'locationplaceNameId'], $place_name_node->getId());
        $place_name_node->relateTo($id_node, 'P1')->save();

        $optional_properties = [
            'P87' => [
                'key' => 'appellation',
                'name' => 'locationplaceNameAppellation',
                'node_type' => 'E44'
            ],
            'P2' => [
                'key' => 'type',
                'name' => 'placeNameType',
                'node_type' => 'E55'
            ],
        ];

        foreach ($optional_properties as $relationship => $optional_property) {
            if (!empty($spatial[$optional_property['key']])) {
                $node = $this->createValueNode(
                    $optional_property['key'],
                    [$optional_property['key'], $optional_property['node_type']],
                    $spatial[$optional_property['key']]
                );

                $place_name_node->relateTo($node, $relationship)->save();
            }
        }

        return $place_name_node;
    }
}

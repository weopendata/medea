<?php

namespace App\Models;

class Location extends Base
{
    public static $NODE_TYPE = 'E53';
    public static $NODE_NAME = 'location';

    protected $implicitModels = [
        'P87' => [
            'key' => 'locationPlaceName',
            'object' => 'Spatial'
        ],
        'P89' => [
            'key' => 'address',
            'object' => 'Address',
            'required' => false
        ]
    ];

    protected $relatedModels = [
    ];

    public function save()
    {
        parent::save();

        // Add an ID to the node
        $client = self::getClient();
        $id_node = $this->createValueNode(['E42', 'locationplaceNameId'], $this->node->getId());

        $this->node->relateTo($id_node, 'P1')->save();
    }

    public function createAddress($address)
    {
        $address_properties = [
            [
                'key' => 'street',
                'object' => 'locationAddressStreet',
                'node_type' => 'E45'
            ],
            [
                'key' => 'number',
                'object' => 'locationAddressNumber',
                'node_type' => 'E45'
            ],
            [
                'key' => 'postalCode',
                'object' => 'locationAddressPostalCode',
                'node_type' => 'E45'
            ],
            [
                'key' => 'locality',
                'object' => 'locationAddressLocality',
                'node_type' => 'E45'
            ]
        ];

        $client = $this->getClient();

        $address_node = $client->makeNode();
        $address_node->save();

        $address_node->addLabels([
            self::makeLabel('E53'), self::makeLabel('locationAddress'), self::makeLabel($this->getGeneralId())
        ]);

        foreach ($address_properties as $address_property) {
            if (!empty($address[$address_property['key']])) {
                $node = $this->createValueNode(
                    [$address_property['object'], $address_property['node_type']],
                    $address[$address_property['key']]
                );

                $address_node->relateTo($node, 'P87')->save();
            }
        }


        return $address_node;
    }

    public function createSpatial($spatial)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        // Make E48 Place name
        $place_name_node = $client->makeNode();
        $place_name_node->save();

        $place_name_node->addLabels([self::makeLabel('E48'), self::makeLabel('locationplaceName')]);

        // Combine E48 with it's explicit ID -> E42 identifier
        $id_node = $this->createValueNode(['E42', 'locationplaceNameId'], $place_name_node->getId());
        $place_name_node->relateTo($id_node, 'P1')->save();

        $optional_properties = [
            'P87' => [
                'key' => 'appellation',
                'object' => 'locationplaceNameAppellation',
                'node_type' => 'E44'
            ],
            'P2' => [
                'key' => 'type',
                'object' => 'placeNameType',
                'node_type' => 'E55'
            ],
        ];

        foreach ($optional_properties as $relationship => $optional_property) {
            if (!empty($spatial[$optional_property['key']])) {
                $node = $this->createValueNode(
                    [$optional_property['object'], $optional_property['node_type']],
                    $spatial[$optional_property['key']]
                );

                $place_name_node->relateTo($node, $relationship)->save();
            }
        }

        if (!empty($spatial['coordinates'])) {
            // Create an E47 Spatial Coordinates node
            $x_coord = $this->createValueNode(['E47', 'locationSpatialCoordinate'], $spatial['coordinates']['x']);
            $y_coord = $this->createValueNode(['E47', 'locationSpatialCoordinate'], $spatial['coordinates']['y']);

            $longitude = $this->createValueNode(['E55', 'locationspatialCoordinateQualifier'], 'longitude');
            $latitude = $this->createValueNode(['E55', 'locationspatialCoordinateQualifier'], 'latitude');

            $x_coord->relateTo($longitude, 'P2')->save();
            $y_coord->relateTo($latitude, 'P2')->save();

            $this->node->relateTo($x_coord, 'P87')->save();
            $this->node->relateTo($y_coord, 'P87')->save();

            if (!empty($spatial['coordinates']['z'])) {
                $z_coord = $this->createValueNode(['E55', 'latitude'], $spatial['coordinates']['y']);
                $altitude = $this->createValueNode(['E55', 'locationspatialCoordinateQualifier'], 'latitude');

                $z_coord->relateTo($altitude, 'P87')->save();
                $this->node->relateTo($z_coord, 'P2')->save();
            }
        }

        return $place_name_node;
    }
}

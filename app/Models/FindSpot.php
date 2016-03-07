<?php

namespace App\Models;

class FindSpot extends Base
{
    public static $NODE_TYPE = 'E27';
    public static $NODE_NAME = 'findSpot';

    protected $implicitModels = [
        'P2' => [
            'key' => 'type',
            'object' => 'Type',
            'value_node' => true,
            'cidoc_type' => 'E55'
        ],

        'P3' => [
            'key' => 'description',
            'object' => 'Note',
            'value_node' => true,
            'cidoc_type' => 'E62'
        ],
        'P1' => [
            'key' => 'title',
            'object' => 'Appellation',
            'value_node' => true,
            'cidoc_type' => 'E41'
        ]
    ];

    protected $relatedModels = [
        'P53' => [
            'key' => 'location',
            'model_name' => 'Location',
            'cascade_delete' => true
        ]
    ];

    public function save()
    {
        parent::save();

        // Add an ID to the node
        $client = self::getClient();
        $id_node = $this->createValueNode(['E42', 'findSpotId'], $this->node->getId());

        $this->node->relateTo($id_node, 'P1')->save();
    }

    public function delete()
    {
        // Get all related nodes through the general id
        $client = $this->getClient();
        $label = $client->makeLabel($this->getGeneralId());

        $related_nodes = $label->getNodes();

        foreach ($related_nodes as $related_node) {
            // Get and delete all of the relationships
            $relationships = $related_node->getRelationships();

            foreach ($relationships as $relationship) {
                $relationship->delete();
            }

            $related_node->delete();
        }

        // Delete the main node
        parent::delete();
    }
}

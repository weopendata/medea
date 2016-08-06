<?php

namespace App\Models;

class FindSpot extends Base
{
    public static $NODE_TYPE = 'E27';

    public static $NODE_NAME = 'findSpot';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P53' => [
            'key' => 'location',
            'model_name' => 'Location',
            'cascade_delete' => true,
            'reverse_relationship' => 'P53'
        ]
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'findSpotType',
                'name' => 'findSpotType',
                'value_node' => true,
                'cidoc_type' => 'E55',
                'required' => false
            ]
        ],

        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'findSpotDescription',
                'name' => 'findSpotDescription',
                'value_node' => true,
                'cidoc_type' => 'E62',
                'required' => false
            ]
        ],
        [
            'relationship' => 'P1',
            'config' => [
                'key' => 'findSpotTitle',
                'name' => 'findSpotTitle',
                'value_node' => true,
                'cidoc_type' => 'E41',
                'required' => false
            ]
        ],
    ];

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

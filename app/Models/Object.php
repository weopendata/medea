<?php

namespace App\Models;

class Object extends Base
{
    public static $NODE_TYPE = 'E22';
    public static $NODE_NAME = 'object';

    protected $has_unique_id = true;

    protected $relatedModels = [
        'P108' => [
            'key' => 'productionEvent',
            'model_name' => 'ProductionEvent',
            'cascade_delete' => true,
            'required' => false
        ]
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P43',
            'config' => [
                'key' => 'dimensions',
                'name' => 'dimension',
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'description',
                'name' => 'objectDescription',
                'value_node' => true,
                'cidoc_type' => 'E62'
            ]
        ],
        [
            'relationship' => 'P45',
            'config' => [
                'key' => 'material',
                'name' => 'objectMaterial',
                'value_node' => true,
                'cidoc_type' => 'E57'
            ]
        ]
    ];

    /**
     * Dimension is not a main entity, so we create it in this object only
     *
     * @param $dimension array An array with value, type, unit
     *
     * @return Node
     */
    public function createDimension($dimension)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        // Make E54 Dimension
        $dimension_node = $client->makeNode();
        $dimension_node->setProperty('name', 'dimension');
        $dimension_node->save();

        // Set the proper labels to the objectDimensionType
        $dimension_node->addLabels([self::makeLabel('E54'), self::makeLabel('objectDimension'), self::makeLabel($general_id)]);

        // Make E55 Type objectDimensionType
        $dimension_type = $this->createValueNode('type', ['E55'], $dimension['type']);
        $dimension_node->relateTo($dimension_type, 'P2')->save();

        // Make E60 Number
        $dimension_value = $this->createValueNode('value', ['E60'], $dimension['value']);
        $dimension_node->relateTo($dimension_value, 'P90')->save();

        // Make E58 Measurement Unit
        $dimension_unit = $this->createValueNode('unit', ['E58'], $dimension['unit']);
        $dimension_node->relateTo($dimension_unit, 'P91')->save();

        return $dimension_node;
    }

    /**
     * Need to override the base delete and delete our relationships with Dimension nodes
     */
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

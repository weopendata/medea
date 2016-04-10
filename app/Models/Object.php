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
            'required' => false,
            'plural' => true,
            'nested' => true
        ],
        'P62' => [
            'key' => 'images',
            'model_name' => 'Photograph',
            'cascade_delete' => true,
            'required' => false,
            'plural' => true
        ],
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P43',
            'config' => [
                'key' => 'dimensions',
                'name' => 'dimensions',
                'plural' => true
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
        ],
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'objectValidationStatus',
                'name' => 'objectValidationStatus',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P108',
            'config' => [
                'key' => 'technique',
                'name' => 'productionEvent',
                'cidoc_type' => 'E12',
                'plural' => false,
                'nested' => true
            ]
        ],
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'category',
                'name' => 'category',
                'value_node' => true,
                'cidoc_type' => 'E55'
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
    public function createDimensions($dimension)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        // Make E54 Dimension
        $dimension_node = $client->makeNode();
        $dimension_node->setProperty('name', 'dimensions');
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

    public function createProductionEvent($technique)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();
        $id_label = $client->makeLabel($general_id);

        // Make E12 production
        $production_node = $client->makeNode();
        $production_node->setProperty('name', 'productionEvent');
        $production_node->save();
        $production_node->addLabels([self::makeLabel('E12'), self::makeLabel('productionEvent'), $id_label]);

        // Make E29 design or procedure
        $production_technique = $client->makeNode();
        $production_technique->setProperty('name', 'productionTechnique');
        $production_technique->save();
        $production_technique->addLabels([self::makeLabel('E29'), self::makeLabel('productionTechnique'), $id_label]);

        $production_node->relateTo($production_technique, 'P33')->save();

        // Make E55 productionType
        $production_type = $this->createValueNode('type', ['E55'], $technique);
        $production_technique->relateTo($production_type, 'P2')->save();

        return $production_node;
    }
}

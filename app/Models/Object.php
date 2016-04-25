<?php

namespace App\Models;

class Object extends Base
{
    public static $NODE_TYPE = 'E22';
    public static $NODE_NAME = 'object';

    protected $has_unique_id = true;

    protected $related_models = [
        'P108' => [
            'key' => 'productionEvent',
            'model_name' => 'ProductionEvent',
            'cascade_delete' => true,
            'required' => false
        ],
        'P62' => [
            'key' => 'photograph',
            'model_name' => 'Photograph',
            'cascade_delete' => true,
            'required' => false,
            'plural' => true
        ],
    ];

    protected $implicit_models = [
        [
            'relationship' => 'P43',
            'config' => [
                'key' => 'dimensions',
                'name' => 'dimensions',
                'plural' => true,
                'cidoc_type' => 'E54'
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'objectDescription',
                'name' => 'objectDescription',
                'value_node' => true,
                'cidoc_type' => 'E62'
            ]
        ],
        [
            'relationship' => 'P45',
            'config' => [
                'key' => 'objectMaterial',
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
            'relationship' => 'P2',
            'config' => [
                'key' => 'category',
                'name' => 'category',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],[
            'relationship' => 'P128',
            'config' => [
                'key' => 'objectInscription',
                'name' => 'objectInscription',
                'cidoc_type' => 'E34'
            ]
        ],
        [
            'relationship' => 'P108',
            'config' => [
                'key' => 'treatmentEvent',
                'name' => 'treatmentEvent',
                'cidoc_type' => 'E11'
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
        $dimension_type = $this->createValueNode('type', ['E55', $general_id], $dimension['type']);
        $dimension_node->relateTo($dimension_type, 'P2')->save();

        // Make E60 Number
        $dimension_value = $this->createValueNode('value', ['E60', $general_id], $dimension['value']);
        $dimension_node->relateTo($dimension_value, 'P90')->save();

        // Make E58 Measurement Unit
        $dimension_unit = $this->createValueNode('unit', ['E58', $general_id], $dimension['unit']);
        $dimension_node->relateTo($dimension_unit, 'P91')->save();

        return $dimension_node;
    }

    /**
     * Create an objectInscription
     */
    public function createObjectInscription($inscription)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        // Make an E34
        $inscription_node = $client->makeNode();
        $inscription_node->setProperty('name', 'objectInscription');
        $inscription_node->save();

        $inscription_node->addLabels([self::makeLabel('E34'), self::makeLabel('objectInscription'), self::makeLabel($general_id)]);

        // Make an E62 string
        $inscription_note_node = $this->createValueNode('objectInscriptionNote', ['E62', $general_id, 'objectInscriptionNote'], $inscription['objectInscriptionNote']);
        $inscription_node->relateTo($inscription_note_node, 'P3')->save();

        return $inscription_node;
    }

    /**
     * Create a treatmentEvent
     */
    public function createTreatmentEvent($treatment)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        // Make a treatmentEvent
        $treatment_node = $client->makeNode();
        $treatment_node->setProperty('name', 'treatmentEvent');
        $treatment_node->save();

        $treatment_node->addLabels([self::makeLabel('E11'), self::makeLabel('treatmentEvent'), self::makeLabel($general_id)]);

        // Make an E29 Design or procedure
        $modification_node = $client->makeNode();
        $modification_node->setProperty('name', 'modificationTechnique');
        $modification_node->save();

        $modification_node->addLabels([self::makeLabel('E29'), self::makeLabel('modificationTechnique'), self::makeLabel($general_id)]);

        $treatment_node->relateTo($modification_node, 'P33')->save();

        // Make a type (modification type)
        $modification_type_node = $this->createValueNode('modificationTechniqueType', ['E55', $general_id, 'modificationTechniqueType'], $treatment['modificationTechnique']['modificationTechniqueType']);

        $modification_node->relateTo($modification_type_node, 'P2')->save();

        return $treatment_node;
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

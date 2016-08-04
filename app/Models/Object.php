<?php

namespace App\Models;

class Object extends Base
{
    public static $NODE_TYPE = 'E22';
    public static $NODE_NAME = 'object';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P108' => [
            'key' => 'productionEvent',
            'model_name' => 'ProductionEvent',
            'cascade_delete' => true,
            'required' => false,
            'reverse_relationship' => 'P108'
        ],
        'P62' => [
            'key' => 'photograph',
            'model_name' => 'Photograph',
            'cascade_delete' => true,
            'required' => false,
            'plural' => true,
            'reverse_relationship' => 'P62'
        ],
    ];

    protected $implicitModels = [
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
        ],
        [
            'relationship' => 'P42',
            'config' => [
                'key' => 'period',
                'name' => 'period',
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

        $generalId = $this->getGeneralId();

        // Make E54 Dimension
        $dimensionNode = $client->makeNode();
        $dimensionNode->setProperty('name', 'dimensions');
        $dimensionNode->save();

        // Set the proper labels to the objectDimensionType
        $dimensionNode->addLabels([self::makeLabel('E54'), self::makeLabel('objectDimension'), self::makeLabel($generalId)]);

        // Make E55 Type objectDimensionType
        $dimension_type = $this->createValueNode('type', ['E55', $generalId], $dimension['type']);
        $dimensionNode->relateTo($dimension_type, 'P2')->save();

        // Make E60 Number
        $dimension_value = $this->createValueNode('value', ['E60', $generalId], $dimension['value']);
        $dimensionNode->relateTo($dimension_value, 'P90')->save();

        // Make E58 Measurement Unit
        $dimension_unit = $this->createValueNode('unit', ['E58', $generalId], $dimension['unit']);
        $dimensionNode->relateTo($dimension_unit, 'P91')->save();

        return $dimensionNode;
    }

    /**
     * Create an objectInscription
     */
    public function createObjectInscription($inscription)
    {
        $client = self::getClient();

        $generalId = $this->getGeneralId();

        // Make an E34
        $inscriptionNode = $client->makeNode();
        $inscriptionNode->setProperty('name', 'objectInscription');
        $inscriptionNode->save();

        $inscriptionNode->addLabels([self::makeLabel('E34'), self::makeLabel('objectInscription'), self::makeLabel($generalId)]);

        // Make an E62 string
        $noteNode = $this->createValueNode('objectInscriptionNote', ['E62', $generalId, 'objectInscriptionNote'], $inscription['objectInscriptionNote']);
        $inscriptionNode->relateTo($noteNode, 'P3')->save();

        return $inscriptionNode;
    }

    /**
     * Create a treatmentEvent
     */
    public function createTreatmentEvent($treatment)
    {
        $client = self::getClient();

        $generalId = $this->getGeneralId();

        if (empty($treatment['modificationTechnique']['modificationTechniqueType'])) {
            return;
        }

        // Make a treatmentEvent
        $treatmentNode = $client->makeNode();
        $treatmentNode->setProperty('name', 'treatmentEvent');
        $treatmentNode->save();

        $treatmentNode->addLabels([self::makeLabel('E11'), self::makeLabel('treatmentEvent'), self::makeLabel($generalId)]);

        // Make an E29 Design or procedure
        $modificationNode = $client->makeNode();
        $modificationNode->setProperty('name', 'modificationTechnique');
        $modificationNode->save();

        $modificationNode->addLabels([
            self::makeLabel('E29'),
            self::makeLabel('modificationTechnique'),
            self::makeLabel($generalId)
        ]);

        $treatmentNode->relateTo($modificationNode, 'P33')->save();

        // Make a type (modification type)
        $typeNode = $this->createValueNode('modificationTechniqueType', ['E55', $generalId, 'modificationTechniqueType'], $treatment['modificationTechnique']['modificationTechniqueType']);

        $modificationNode->relateTo($typeNode, 'P2')->save();

        return $treatmentNode;
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

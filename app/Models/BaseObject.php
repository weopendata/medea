<?php

namespace App\Models;

use App\Repositories\CollectionRepository;
use App\Services\NodeService;
use Illuminate\Support\Arr;

/**
 * Class BaseObject
 * This class used to be called "Object" to match the taxonomy used in the MEDEA project
 * However, recent PHP versions have Object as an occupied name so BaseObject was the refactored name for this class.
 *
 * @package App\Models
 */
class BaseObject extends Base
{
    public static $NODE_TYPE = 'E22';
    public static $NODE_NAME = 'object';

    protected $hasUniqueId = true;

    protected $properties = [
        [
            'name' => 'feedback',
        ],
        [
            'name' => 'embargo',
            'default_value' => 'false'
        ],
        [
            'name' => 'validated_at',
        ],
        [
            'name' => 'validated_by'
        ],
        [
            'name' => 'classifiable'
        ]
    ];

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
        'P24' => [
            'key' => 'collection',
            'model_name' => 'Collection',
            'cascade_delete' => false,
            'link_only' => true,
            'reverse_relationship' => 'P46'
        ],
        'P157' => [
            'key' => 'context',
            'model_name' => 'Context',
            'cascade_delete' => false,
            'link_only' => true,
            'reverse_relationship' => 'P157',
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
            'relationship' => 'P56',
            'config' => [
                'key' => 'distinguishingFeatures',
                'name' => 'distinguishingFeatures',
                'plural' => true,
                'cidoc_type' => 'E25'
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
            'relationship' => 'P57',
            'config' => [
                'key' => 'objectNumberOfParts',
                'name' => 'objectNumberOfParts',
                'value_node' => true,
                'cidoc_type' => 'E60'
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
                'key' => 'objectCategory',
                'name' => 'objectCategory',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ], [
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
        ],
        [
            'relationship' => 'P1',
            'config' => [
                'key' => 'objectNr',
                'name' => 'objectNr',
                'value_node' => true,
                'cidoc_type' => 'E42'
            ]
        ],
    ];

    /**
     * Overwrite the constructor and construct a full text field
     * after construction of the subtree
     *
     * @param array $properties
     *
     * @return Base
     * @throws \Everyman\Neo4j\Exception
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);

        if (! empty($properties)) {
            $this->updateFtsField($properties);
        }
    }

    /**
     * Force the full text field to be recomputed
     *
     * @return void
     * @throws \Everyman\Neo4j\Exception
     */
    public function computeFtsField()
    {
        $object = $this->getValues();

        $this->updateFtsField($object);
    }

    /**
     * Fill in the Fulltest Search field
     *
     * @param array $properties The properties assigned to an Object node
     *
     * @return void
     * @throws \Everyman\Neo4j\Exception
     */
    private function updateFtsField($properties)
    {
        $fulltextProperties = [
            'objectCategory',
            'objectNr',
            'objectDescription',
            'material',
            'surfaceTreatment',
            'treatmentEvent.modificationTechnique.modificationTechniqueType',
            'productionEvent.productionTechnique.productionTechniqueType',
        ];

        $description = '';

        foreach ($fulltextProperties as $property) {
            if (! empty($value = Arr::get($properties, $property))) {
                $description .= $value . ' ';
            }
        }

        // Check if there's a collection linked to this object, if so, then add the title to the FTS field
        $collection = app(CollectionRepository::class)->getCollectionForObject($this->node->getId());

        if (! empty($collection['title'])) {
            $description .= ' ' . $collection['title'];
        }

        // Through recursion we can safely say that the ID of the find will be one less
        // than the ID of object, this is not a very safe way though
        // The field should be set through the repository instead of in this object
        $description .= $this->getNode()->getId() - 1;

        $this->getNode()->setProperty('fulltext_description', $description)->save();
    }

    public function update($properties)
    {
        parent::update($properties);

        if (! empty($properties)) {
            $this->updateFtsField($properties);
        }
    }

    /**
     * Dimension is not a main entity, so we create it in this object only
     *
     * @param $dimension array An array with value, type, unit
     * @return \Everyman\Neo4j\Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function createDimensions($dimension)
    {
        $generalId = $this->getGeneralId();

        // Make E54 Dimension
        $dimensionNode = NodeService::makeNode();
        $dimensionNode->setProperty('name', 'dimensions');
        $dimensionNode->save();

        // Set the proper labels to the objectDimensionType
        $dimensionNode->addLabels([self::makeLabel('E54'), self::makeLabel('objectDimension'), self::makeLabel($generalId)]);

        // Make E55 Type objectDimensionType
        $dimensionType = $this->createValueNode('dimensionType', ['E55', $generalId], $dimension['dimensionType']);
        $dimensionNode->relateTo($dimensionType, 'P2')->save();

        // Make E60 Number
        $dimensionValue = $this->createValueNode('measurementValue', ['E60', $generalId], $dimension['measurementValue']);
        $dimensionNode->relateTo($dimensionValue, 'P90')->save();

        // Make E58 Measurement Unit
        $dimensionUnit = $this->createValueNode('dimensionUnit', ['E58', $generalId], $dimension['dimensionUnit']);
        $dimensionNode->relateTo($dimensionUnit, 'P91')->save();

        return $dimensionNode;
    }

    public function createDistinguishingFeatures($distinguishingFeature)
    {
        $generalId = $this->getGeneralId();

        $distinguishingFeatureNode = NodeService::makeNode();
        $distinguishingFeatureNode->setProperty('name', 'distinguishingFeatures');
        $distinguishingFeatureNode->save();

        $distinguishingFeatureNode->addLabels([self::makeLabel('E25'), self::makeLabel('distinguishingFeature'), self::makeLabel($generalId)]);

        $type = $this->createValueNode('distinguishingFeatureType', ['E55', $generalId], $distinguishingFeature['distinguishingFeatureType']);
        $distinguishingFeatureNode->relateTo($type, 'P2')->save();

        $value = $this->createValueNode('distinguishingFeatureNote', ['E62', $generalId], $distinguishingFeature['distinguishingFeatureNote']);
        $distinguishingFeatureNode->relateTo($value, 'P3')->save();

        return $distinguishingFeatureNode;
    }

    /**
     * Create an objectInscription
     */
    public function createObjectInscription($inscription)
    {
        $generalId = $this->getGeneralId();

        // Make an E34
        $inscriptionNode = NodeService::makeNode();
        $inscriptionNode->setProperty('name', 'objectInscription');
        $inscriptionNode->save();

        $inscriptionNode->addLabels([self::makeLabel('E34'), self::makeLabel('objectInscription'), self::makeLabel($generalId)]);

        // Make an E62 string (Note)
        $noteNode = $this->createValueNode('objectInscriptionNote', ['E62', $generalId, 'objectInscriptionNote'], $inscription['objectInscriptionNote']);

        // Relate the created nodes to the main inscription Node
        $inscriptionNode->relateTo($noteNode, 'P3')->save();

        if (! empty($inscription['objectInscriptionType'])) {
            $typeNode = $this->createValueNode('objectInscriptionType', ['E55', $generalId, 'objectInscriptionType'], $inscription['objectInscriptionType']);
            $inscriptionNode->relateTo($typeNode, 'P2')->save();
        }

        if (! empty($inscription['objectInscriptionLocation'])
            && ! empty($inscription['objectInscriptionLocation']['inscriptionLocationAppellation'])) {
            $locationNode = $this->createValueNode('objectInscriptionLocation', ['E53', $generalId, 'objectInscriptionLocation'], 'objectInscriptionLocation');

            $appellationNode = $this->createValueNode(
                'inscriptionLocationAppellation',
                ['E44', 'inscriptionLocationAppellation', $generalId],
                $inscription['objectInscriptionLocation']['inscriptionLocationAppellation']
            );

            $locationNode->relateTo($appellationNode, 'P87')->save();
            $inscriptionNode->relateTo($locationNode, 'P59')->save();
        }

        return $inscriptionNode;
    }

    /**
     * Create a treatmentEvent
     */
    public function createTreatmentEvent($treatment)
    {
        $generalId = $this->getGeneralId();

        if (empty($treatment['modificationTechnique']['modificationTechniqueType'])) {
            return;
        }

        // Make a treatmentEvent
        $treatmentNode = NodeService::makeNode();
        $treatmentNode->setProperty('name', 'treatmentEvent');
        $treatmentNode->save();

        $treatmentNode->addLabels([self::makeLabel('E11'), self::makeLabel('treatmentEvent'), self::makeLabel($generalId)]);

        // Make an E29 Design or procedure
        $modificationNode = NodeService::makeNode();
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

        $related_nodes = NodeService::getNodesForLabel($label);

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

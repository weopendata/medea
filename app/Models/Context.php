<?php


namespace App\Models;


use App\Services\NodeService;

class Context extends Base
{
    public static $NODE_TYPE = 'S22';
    public static $NODE_NAME = 'context';

    protected $hasUniqueId = true;

    protected $properties = [
        [
            'name' => 'internalId' // An ID used to uniquely identify the find without the internal Neo4J ID
        ],
        [
            'name' => 'excavationId', // The global unique ID of the linked excavation
        ],
        [
            'name' => 'local_context_id' // C0, C1, ...
        ]
    ];

    protected $relatedModels = [
        'O22' => [
            'key' => 'context',
            'model_name' => 'Context',
            'cascade_delete' => false,
            'reverse_relationship' => '',
            // DO NOT ENTER A REVERSE RELATIONSHIP, the recursion does not take this kind of relationship into account, the kind where models refer to themselves again
            'required' => false,
            'link_only' => true
        ],
        'P53' => [
            'key' => 'searchArea',
            'model_name' => 'SearchArea',
            'cascade_delete' => true,
            'link_only' => true,
            'required' => false,
        ]
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P140',
            'config' => [
                'key' => 'contextId',
                'name' => 'contextId',
                'cidoc_type' => 'E15'
            ]
        ],
        [
            'relationship' => 'P140',
            'config' => [
                'key' => 'contextLegacyId',
                'name' => 'contextLegacyId',
                'cidoc_type' => 'E15'
            ]
        ],
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'contextType',
                'name' => 'contextType',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P41',
            'config' => [
                'key' => 'contextCharacter',
                'name' => 'contextCharacter',
                'cidoc_type' => 'E17'
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'contextInterpretation',
                'name' => 'contextInterpretation',
                'value_node' => true,
                'cidoc_type' => 'E62'
            ]
        ],
        [
            'relationship' => 'P140',
            'config' => [
                'key' => 'contextDating',
                'name' => 'contextDating',
                'cidoc_type' => 'E13',
            ]
        ]
    ];

    /**
     * @param string $localContextId i.e. C0, C1, ...
     * @param string $excavationId
     * @return string
     */
    public static function createInternalId($localContextId, $excavationId)
    {
        return $excavationId . '__' . $localContextId;
    }

    /**
     * @param $contextId
     * @return \Everyman\Neo4j\Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function createContextId($contextId)
    {
        $generalId = $this->getGeneralId();

        $contextIdNode = NodeService::makeNode();
        $contextIdNode->setProperty('name', 'contextId');
        $contextIdNode->save();
        $contextIdNode->addLabels([
            self::makeLabel('E15'),
            self::makeLabel('contextId'),
            self::makeLabel($generalId)
        ]);

        $contextIdValueNode = $this->createValueNode(
            'contextIdValue',
            ['E42', $generalId, 'contextIdValue'],
            $contextId['contextIdValue']
        );

        // Create the relationship
        $contextIdNode->relateTo($contextIdValueNode, 'P37')->save();

        // Add the type to the contextIdValueNode
        $typeNode = $this->createValueNode(
            'contextIdType',
            ['E55', $generalId, 'contextIdType'],
            'contextID'
        );

        $contextIdValueNode->relateTo($typeNode, 'P2')->save();

        return $contextIdNode;
    }

    public function createContextLegacyId($contextLegacyId)
    {
        if (empty($contextLegacyId)) {
            return;
        }

        $generalId = $this->getGeneralId();

        $contextLegacyIdNode = NodeService::makeNode();
        $contextLegacyIdNode->setProperty('name', 'contextLegacyId');
        $contextLegacyIdNode->save();
        $contextLegacyIdNode->addLabels([
            self::makeLabel('E15'),
            self::makeLabel('contextLegacyId'),
            self::makeLabel($generalId)
        ]);

        $contextIdValueNode = $this->createValueNode(
            'contextLegacyIdValue',
            ['E42', $generalId, 'contextLegacyIdValue'],
            $contextLegacyId['contextLegacyIdValue']
        );

        // Create the relationship
        $contextLegacyIdNode->relateTo($contextIdValueNode, 'P37')->save();

        $typeNode = $this->createValueNode(
            'contextIdType',
            ['E55', $generalId, 'contextIdType'],
            'contextLegacyID'
        );

        $contextIdValueNode->relateTo($typeNode, 'P2')->save();

        return $contextLegacyIdNode;
    }

    public function createContextCharacter($contextCharacter)
    {
        if (empty($contextCharacter['contextCharacterType'])) {
            return;
        }

        $generalId = $this->getGeneralId();

        $contextCharacterNode = NodeService::makeNode();
        $contextCharacterNode->setProperty('name', 'contextCharacter');
        $contextCharacterNode->save();
        $contextCharacterNode->addLabels([
            self::makeLabel('E17'),
            self::makeLabel('contextCharacter'),
            self::makeLabel($generalId)
        ]);

        $contextCharacterType = $this->createValueNode(
            'contextCharacterType',
            ['E55', $generalId, 'contextCharacterType'],
            $contextCharacter['contextCharacterType']
        );

        // Create the relationship
        $contextCharacterNode->relateTo($contextCharacterType, 'P42')->save();

        return $contextCharacterNode;
    }

    public function createContextDating($contextDating)
    {
        if (empty($contextDating['contextDatingPeriod']) || empty($contextDating['contextDatingPeriod']['value'])) {
            return;
        }

        $generalId = $this->getGeneralId();

        $contextDatingNode = NodeService::makeNode();
        $contextDatingNode->setProperty('name', 'contextDating');
        $contextDatingNode->save();
        $contextDatingNode->addLabels([
            self::makeLabel('E13'),
            self::makeLabel('contextDating'),
            self::makeLabel($generalId)
        ]);

        // Create the period
        $contextDatingPeriod = $this->createValueNode(
            'contextDatingPeriod',
            ['E52', $generalId, 'contextDatingPeriod'],
            $contextDating['contextDatingPeriod']['value']
        );

        $contextDatingNode->relateTo($contextDatingPeriod, 'P140')->save();

        // Add several attributes to this period node
        if (!empty($contextDating['contextDatingPeriod']['contextDatingPeriodPrecision'])) {
            $contextDatingPeriodPrecision = $this->createValueNode(
                'contextDatingPeriodPrecision',
                ['E52', $generalId, 'contextDatingPeriodPrecision'],
                $contextDating['contextDatingPeriod']['contextDatingPeriodPrecision']
            );

            $contextDatingPeriod->relateTo($contextDatingPeriodPrecision, 'P2')->save();
        }

        if (!empty($contextDating['contextDatingPeriod']['contextDatingPeriodNature'])) {
            $contextDatingPeriodNature = $this->createValueNode(
                'contextDatingPeriodNature',
                ['E52', $generalId, 'contextDatingPeriodNature'],
                $contextDating['contextDatingPeriod']['contextDatingPeriodNature']
            );

            $contextDatingPeriod->relateTo($contextDatingPeriodNature, 'P2')->save();
        }

        if (!empty($contextDating['contextDatingTechnique']['contextDatingPeriodMethod'])) {
            $contextDatingTechnique = $this->createValueNode(
                'contextDatingTechnique',
                ['E29', $generalId, 'contextDatingTechnique'],
                ''
            );

            $contextDatingNode->relateTo($contextDatingTechnique, 'P33')->save();

            $contextDatingPeriodMethod = $this->createValueNode(
                'contextDatingPeriodMethod',
                ['E29', $generalId, 'contextDatingPeriodMethod'],
                $contextDating['contextDatingTechnique']['contextDatingPeriodMethod']
            );

            $contextDatingTechnique->relateTo($contextDatingPeriodMethod, 'P2')->save();
        }

        if (!empty($contextDating['contextDatingRemark'])) {
            $contextDatingRemark = $this->createValueNode(
                'contextDatingRemark',
                ['E62', $generalId, 'contextDatingRemark'],
                $contextDating['contextDatingRemark']
            );

            $contextDatingNode->relateTo($contextDatingRemark, 'P3')->save();
        }

        return $contextDatingNode;
    }
}

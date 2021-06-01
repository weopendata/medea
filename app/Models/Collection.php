<?php

namespace App\Models;

use App\Services\NodeService;

class Collection extends Base
{
    public static $NODE_TYPE = 'E78';
    public static $NODE_NAME = 'collection';

    protected $hasUniqueId = true;

    protected $relatedModels = [
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P109',
            'config' => [
                'key' => 'institution',
                'name' => 'institution',
                'plural' => true,
                'cidoc_type' => 'E40'
            ]
        ]
    ];

    /**
     * Dimension is not a main entity, so we create it in this object only
     *
     * @param array $institutions
     *
     * @return Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function createInstitution($institution)
    {
        $generalId = $this->getGeneralId();

        // Make E40 Institution
        $institutionNode = NodeService::makeNode();
        $institutionNode->setProperty('name', 'institution');
        $institutionNode->save();

        // Set the proper labels to the objectDimensionType
        $institutionNode->addLabels([self::makeLabel('E40'), self::makeLabel('LegalBody'), self::makeLabel($generalId)]);

        // Make E82
        $institutionAppellation = $this->createValueNode('institutionAppellation', ['E82', $generalId], $institution['institutionAppellation']);
        $institutionNode->relateTo($institutionAppellation, 'P131')->save();

        return $institutionNode;
    }

    /**
     * @param string $excavationId
     * @return string
     */
    public static function createInternalId(string $excavationId)
    {
        return $excavationId . '__Collection';
    }

    /**
     * @var array 
     */
    protected $properties = [
        [
            'name' => 'title'
        ],
        [
            'name' => 'description'
        ],
        [
            'name' => 'collectionType'
        ],
        [
            'name' => 'internalId',
        ]
    ];
}

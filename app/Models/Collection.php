<?php

namespace App\Models;

use App\Services\NodeService;

class Collection extends Base
{
    public static $NODE_TYPE = 'E78';
    public static $NODE_NAME = 'collection';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P109' => [
            'key' => 'group',
            'model_name' => 'Group',
            'cascade_delete' => false,
            'required' => false,
            'plural' => false,
            'link_only' => true
        ],
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
        ],
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'collectionType',
                'name' => 'collectionType',
                'plural' => false,
                'cidoc_type' => 'E42',
                'value_node' => true
            ]
        ],
    ];

    /**
     * @param array $institutions
     *
     * @return Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function createInstitution($institution)
    {
        $generalId = $this->getGeneralId();

        // Make E40 Group
        $institutionNode = NodeService::makeNode();
        $institutionNode->setProperty('name', 'institution');
        $institutionNode->save();

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
            'name' => 'internalId',
        ]
    ];
}

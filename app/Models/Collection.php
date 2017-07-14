<?php

namespace App\Models;

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
     */
    public function createInstitution($institution)
    {
        $client = self::getClient();

        $generalId = $this->getGeneralId();

        // Make E40 Institution
        $institutionNode = $client->makeNode();
        $institutionNode->setProperty('name', 'institution');
        $institutionNode->save();

        // Set the proper labels to the objectDimensionType
        $institutionNode->addLabels([self::makeLabel('E40'), self::makeLabel('LegalBody'), self::makeLabel($generalId)]);

        // Make E82
        $institutionAppellation = $this->createValueNode('institutionAppellation', ['E82', $generalId], $institution['institutionAppellation']);
        $institutionNode->relateTo($institutionAppellation, 'P131')->save();

        return $institutionNode;
    }

    protected $properties = [
        [
            'name' => 'title'
        ],
        [
            'name' => 'description'
        ],
        [
            'name' => 'collectionType'
        ]
    ];
}

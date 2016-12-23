<?php

namespace App\Models;

class Publication extends Base
{
    public static $NODE_TYPE = 'E31';
    public static $NODE_NAME = 'publication';

    protected $hasUniqueId = true;

    protected $relatedModels = [
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P102',
            'config' => [
                'key' => 'publicationTitle',
                'name' => 'publicationTitle',
                'value_node' => true,
                'cidoc_type' => 'E35'
            ]
        ],
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'publicationType',
                'name' => 'publicationType',
                'value_node' => true,
                'cidoc_type' => 'E55',
            ]
        ],
        [
            'relationship' => 'P94',
            'config' => [
                'key' => 'publicationCreations',
                'name' => 'publicationCreations',
                'plural' => true,
                'cidoc_type' => 'E31',
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'publicationPages',
                'name' => 'publicationPages',
                'value_node' => true,
                'cidoc_type' => 'E62',
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'publicationVolume',
                'name' => 'publicationVolume',
                'value_node' => true,
                'cidoc_type' => 'E62',
            ]
        ],
        [
            'relationship' => 'P76',
            'config' => [
                'key' => 'publicationContact',
                'name' => 'publicationContact',
                'value_node' => true,
                'cidoc_type' => 'E51',
            ]
        ],
    ];

    protected $properties = [
    ];

    public function createPublicationCreation($publicationCreation)
    {
        $client = self::getClient();

        $generalId = $this->getGeneralId();

        // Create a publicationCreation (E65)
        $pubCreationNode = $client->makeNode();
        $pubCreationNode->setProperty('name', 'publicationCreations');
        $pubCreationNode->save();
        $pubCreationNode->addLabels(
            self::makeLabel('E65'),
            self::makeLabel('publicationCreation'),
            self::makeLabel($generalId)
        );

        // Create a publicationCreationActor (E39)
        $pubCreationActorNode = $client->makeNode();
        $pubCreationActorNode->setProperty('name', 'publicationCreationActor');
        $pubCreationActorNode->save();
        $pubCreationActorNode->addLabels(
            self::makeLabel('E39'),
            self::makeLabel('publicationCreationActor'),
            self::makeLabel($generalId)
        );

        // Link the actor with the publicationCreation
        $pubCreationNode->relateTo($pubCreationActorNode, 'P14')->save();

        // Create the type and name of the actor
        $actorNameNode = $this->createValueNode(
            'publicationCreationActorName',
            ['E82', $generalId, 'publicationCreationActorName'],
            $publicationCreation['publicationCreationActor']['publicationCreationActorName']
        );

        $actorTypeNode = $this->createValueNode(
            'publicationCreationActorType',
            ['E55', $generalId, 'publicationCreationActorType'],
            $publicationCreation['publicationCreationActor']['publicationCreationActorType']
        );

        $pubCreationActorNode->relateTo($actorNameNode, 'P131')->save();
        $pubCreationActorNode->relateTo($actorTypeNode, 'P141')->save();

        // Save optional data (publicationCreationTimeSpan, publicationCreationLocation)
        if (! empty($publicationCreation['']))

        return $publicationCreation;
    }
}

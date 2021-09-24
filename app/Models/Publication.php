<?php

namespace App\Models;

use App\Services\NodeService;

class Publication extends Base
{
    public static $NODE_TYPE = 'E31';
    public static $NODE_NAME = 'publication';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P106' => [
            'key' => 'publication',
            'model_name' => 'Publication',
            'cascade_delete' => true,
            'required' => false,
            'reverse_relationship' => ''
        ],
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P1',
            'config' => [
                'key' => 'publicationResearchURI',
                'name' => 'publicationResearchURI',
                'value_node' => true,
                'cidoc_type' => 'E42'
            ]
        ],
        [
            'relationship' => 'P1',
            'config' => [
                'key' => 'publicationArchiveURI',
                'name' => 'publicationArchiveURI',
                'value_node' => true,
                'cidoc_type' => 'E42'
            ]
        ],
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
                'key' => 'publicationCreation',
                'name' => 'publicationCreation',
                'plural' => true,
                'cidoc_type' => 'E65',
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

    public function createPublicationCreation($publicationCreation)
    {
        $generalId = $this->getGeneralId();

        // Create a publicationCreation (E65)
        $pubCreationNode = NodeService::makeNode();
        $pubCreationNode->setProperty('name', 'publicationCreation');
        $pubCreationNode->save();
        $pubCreationNode->addLabels([
            self::makeLabel('E65'),
            self::makeLabel('publicationCreation'),
            self::makeLabel($generalId)
        ]);

        // Create the type and name of the actor
        if (! empty($creationActors = $publicationCreation['publicationCreationActor'])) {
            foreach ($creationActors as $creationActor) {
                // Create a publicationCreationActor (E39)
                $pubCreationActorNode = NodeService::makeNode();
                $pubCreationActorNode->setProperty('name', 'publicationCreationActor');
                $pubCreationActorNode->save();
                $pubCreationActorNode->addLabels([
                    self::makeLabel('E39'),
                    self::makeLabel('publicationCreationActor'),
                    self::makeLabel($generalId)
                    ]);

                // Link the actor with the publicationCreation
                $pubCreationNode->relateTo($pubCreationActorNode, 'P14')->save();

                $actorNameNode = $this->createValueNode(
                    'publicationCreationActorName',
                    ['E82', $generalId, 'publicationCreationActorName'],
                    $creationActor['publicationCreationActorName']
                );

                $actorTypeNode = $this->createValueNode(
                    'publicationCreationActorType',
                    ['E55', $generalId, 'publicationCreationActorType'],
                    $creationActor['publicationCreationActorType']
                );

                $pubCreationActorNode->relateTo($actorTypeNode, 'P141')->save();
                $pubCreationActorNode->relateTo($actorNameNode, 'P131')->save();
            }
        }

        // Save optional data (publicationCreationTimeSpan, publicationCreationLocation)
        if (! empty($publicationCreation['publicationCreationTimeSpan'])) {
            $pubCreationTsNode = NodeService::makeNode();
            $pubCreationTsNode->setProperty('name', 'publicationCreationTimeSpan');
            $pubCreationTsNode->save();
            $pubCreationTsNode->addLabels([
                self::makeLabel('E53'),
                self::makeLabel('publicationCreationTimeSpan'),
                self::makeLabel($generalId)
            ]);

            $pubCreationNode->relateTo($pubCreationTsNode, 'P4')->save();

            $dateNode = $this->createValueNode(
                'date',
                ['E50', $generalId, 'date'],
                $publicationCreation['publicationCreationTimeSpan']['date']
            );

            $pubCreationTsNode->relateTo($dateNode, 'P78')->save();
        }

        if (! empty($publicationCreation['publicationCreationLocation'])) {
            $pubCreationLocation = NodeService::makeNode();
            $pubCreationLocation->setProperty('name', 'publicationCreationLocation');
            $pubCreationLocation->save();
            $pubCreationLocation->addLabels([
                self::makeLabel('E53'),
                self::makeLabel('publicationCreationLocation'),
                self::makeLabel($generalId)
            ]);

            $pubCreationNode->relateTo($pubCreationLocation, 'P7')->save();

            $locationAppellation = $this->createValueNode(
                'publicationCreationLocationAppellation',
                ['E44', $generalId, 'publicationCreationLocationAppellation'],
                $publicationCreation['publicationCreationLocation']['publicationCreationLocationAppellation']
            );

            $pubCreationLocation->relateTo($locationAppellation, 'P87')->save();
        }

        return $pubCreationNode;
    }
}

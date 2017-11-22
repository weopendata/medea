<?php

namespace App\Models;

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

    protected $properties = [
    ];

    public function createPublicationCreation($publicationCreation)
    {
        $client = self::getClient();

        $generalId = $this->getGeneralId();

        // Create a publicationCreation (E65)
        $pubCreationNode = $client->makeNode();
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
                $pubCreationActorNode = $client->makeNode();
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
            $pubCreationTsNode = $client->makeNode();
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
            $pubCreationLocation = $client->makeNode();
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

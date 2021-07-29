<?php

namespace App\Models;

use App\Services\NodeService;

class Photograph extends Base
{
    public static $NODE_TYPE = 'E38';
    public static $NODE_NAME = 'photograph';

    protected $relatedModels = [
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P104',
            'config' => [
                'key' => 'photographRights',
                'name' => 'photographRights',
                'value_node' => false,
                'cidoc_type' => 'E30'
            ]
        ],
    ];

    protected $properties = [
        [
            'name' => 'resized',
        ],
        [
            'name' => 'width'
        ],
        [
            'name' => 'height'
        ],
        [
            'name' => 'src'
        ],
        [
            'name' => 'remarks'
        ]
    ];

    public function createPhotographRights($data)
    {
        if (empty($data)) {
            return;
        }

        // Make E30 Right
        $photographRights = NodeService::makeNode();
        $photographRights->setProperty('name', 'photographRights');
        $photographRights->save();
        $photographRights->addLabels(
            [
                self::makeLabel('E30'),
                self::makeLabel('photographRights'),
                self::makeLabel($this->getGeneralId())
            ]
        );

        $namesOfPhotographRights = [
            'photographRightsAttribution',
            'photographRightsLicense',
        ];

        foreach ($namesOfPhotographRights as $photographRight) {
            if (empty($data[$photographRight])) {
                continue;
            }

            $rightNode = $this->createValueNode($photographRight, ['E41', $this->getGeneralId()], $data[$photographRight]);
            $photographRights->relateTo($rightNode, 'P1')->save();
        }

        return $photographRights;
    }
}

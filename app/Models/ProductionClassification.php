<?php

namespace App\Models;

class ProductionClassification extends Base
{
    public static $NODE_TYPE = 'E17';
    public static $NODE_NAME = 'productionClassification';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P108' => [
            'key' => 'publication',
            'model_name' => 'Publication',
            'cascade_delete' => true,
            'required' => false,
            'plural' => true,
            'reverse_relationship' => 'P67'
        ],
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'type',
                'name' => 'type',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'description',
                'name' => 'description',
                'value_node' => true,
                'cidoc_type' => 'E62'
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
            'relationship' => 'P42',
            'config' => [
                'key' => 'nation',
                'name' => 'nation',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ]
    ];

    protected $properties = [
        [
            'name' => 'agree',
            'default_value' => 0
        ],
        [
            'name' => 'disagree',
            'default_value' => 0
        ],
        [
            'name' => 'startDate'
        ],
        [
            'name' => 'endDate'
        ]
    ];
}

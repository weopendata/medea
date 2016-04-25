<?php

namespace App\Models;

class ProductionClassification extends Base
{
    public static $NODE_TYPE = 'E17';
    public static $NODE_NAME = 'productionClassification';

    protected $has_unique_id = true;

    protected $related_models = [
    ];

    protected $implicit_models = [
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
                'key' => 'culture',
                'name' => 'culture',
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
        ]
    ];
}

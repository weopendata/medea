<?php

namespace App\Models;

class FindEvent extends Base
{
    public static $NODE_TYPE = 'E10';
    public static $NODE_NAME = 'findEvent';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P12' => [
            'key' => 'object',
            'model_name' => 'Object',
            'cascade_delete' => true,
            'reverse_relationship' => 'P12'
        ],
        'P7' => [
            'key' => 'findSpot',
            'model_name' => 'FindSpot',
            'cascade_delete' => true,
            'reverse_relationship' => 'P7'
        ],
        'P29' => [
            'key' => 'person',
            'model_name' => 'Person',
            'cascade_delete' => false,
            'link_only' => true,
            'reverse_relationship' => 'P29'
        ]
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P4',
            'config' => [
                'key' => 'findDate',
                'name' => 'findDate',
                'value_node' => true,
                'cidoc_type' => 'E52'
            ]
        ],
    ];
}

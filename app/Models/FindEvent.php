<?php

namespace App\Models;

class FindEvent extends Base
{
    public static $NODE_TYPE = 'E10';
    public static $NODE_NAME = 'findEvent';

    protected $has_unique_id = true;

    protected $related_models = [
        'P12' => [
            'key' => 'object',
            'model_name' => 'Object',
            'cascade_delete' => true
        ],
        'P7' => [
            'key' => 'findSpot',
            'model_name' => 'FindSpot',
            'cascade_delete' => true
        ],
        'P29' => [
            'key' => 'person',
            'model_name' => 'Person',
            'cascade_delete' => false,
            'link_only' => true,
            'reverse_relationhip' => 'P29'
        ]
    ];

    protected $implicit_models = [
        [
            'relationship' => 'P4',
            'config' => [
                'key' => 'findDate',
                'name' => 'findPeriod',
                'value_node' => true,
                'cidoc_type' => 'E52'
            ]
        ],
    ];
}

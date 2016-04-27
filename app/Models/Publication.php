<?php

namespace App\Models;

class Publication extends Base
{
    public static $NODE_TYPE = 'E31';
    public static $NODE_NAME = 'publication';

    protected $has_unique_id = true;

    protected $related_models = [
    ];

    protected $implicit_models = [
        [
            'relationship' => 'P102',
            'config' => [
                'key' => 'publicationTitle',
                'name' => 'publicationTitle',
                'value_node' => true,
                'cidoc_type' => 'E35'
            ]
        ]
    ];

    protected $properties = [
    ];
}

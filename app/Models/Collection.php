<?php

namespace App\Models;

class Collection extends Base
{
    public static $NODE_TYPE = 'E78';
    public static $NODE_NAME = 'collection';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P46' => [
            'key' => 'object',
            'model_name' => 'Object',
            'cascade_delete' => false,
            'reverse_relationship' => 'P24',
        ],
    ];

    protected $properties = [
        [
            'name' => 'title'
        ],
        [
            'name' => 'description'
        ],
        [
            'name' => 'type'
        ]
    ];
}

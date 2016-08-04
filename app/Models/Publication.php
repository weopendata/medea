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
        ]
    ];

    protected $properties = [
    ];
}

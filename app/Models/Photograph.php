<?php

namespace App\Models;

class Photograph extends Base
{
    public static $NODE_TYPE = 'E38';
    public static $NODE_NAME = 'photograph';

    protected $related_models = [
    ];

    protected $implicit_models = [
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
        ]
    ];
}

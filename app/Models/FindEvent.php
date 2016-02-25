<?php

namespace App\Models;

class FindEvent extends Base
{
    protected static $fillable = ['date'];

    public static $NODE_TYPE = 'E10';
    public static $NODE_NAME = 'findEvent';

    protected $relatedModels = [
        'P12' => [
            'key' => 'object',
            'model_name' => 'Object',
            'cascade_delete' => true
        ]
    ];
}

<?php

namespace App\Models;

class ProductionEvent extends Base
{
    public static $NODE_TYPE = 'E12';
    public static $NODE_NAME = 'productionEvent';

    protected $related_models = [
        'P41' => [
            'key' => 'classification',
            'model_name' => 'ProductionClassification',
            'cascade_delete' => true
        ]
    ];
}

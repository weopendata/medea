<?php

namespace App\Models;

class ProductionClassification extends Base
{
    public static $NODE_TYPE = 'E17';
    public static $NODE_NAME = 'productionClassification';

    protected $relatedModels = [

    ];

    protected $implicitModels = [
        'P2' => [
            'key' => 'type',
            'object' => 'Type',
            'single_value' => true,
            'entity_name' => 'E55'
        ],
        'P3' => [
            'key' => 'description',
            'object' => 'Description',
            'single_value' => true,
            'entity_name' => 'E62'
        ],
        'P42' => [
            'key' => 'culture',
            'object' => 'Culture',
            'single_value' => true,
            'entity_name' => 'E55'
        ],
        'P42' => [
            'key' => 'nation',
            'object' => 'Nation',
            'single_value' => true,
            'entity_name' => 'E55'
        ]
    ];
}

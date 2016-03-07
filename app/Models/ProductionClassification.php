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
            'value_node' => true,
            'cidoc_type' => 'E55'
        ],
        'P3' => [
            'key' => 'description',
            'object' => 'Description',
            'value_node' => true,
            'cidoc_type' => 'E62'
        ],
        'P42' => [
            'key' => 'culture',
            'object' => 'Culture',
            'value_node' => true,
            'cidoc_type' => 'E55'
        ],
        'P42' => [
            'key' => 'nation',
            'object' => 'Nation',
            'value_node' => true,
            'cidoc_type' => 'E55'
        ]
    ];
}

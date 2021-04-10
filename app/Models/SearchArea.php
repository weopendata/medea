<?php


namespace App\Models;


class SearchArea extends Base
{
    public static $NODE_TYPE = 'E27';
    public static $NODE_NAME = 'searchArea';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P53' => [
            'key' => 'location',
            'model_name' => 'Location',
            'cascade_delete' => true,
            'reverse_relationship' => 'P53'
        ],
        'P7' => [
            'key' => 'searchEvent',
            'model_name' => 'SearchEvent',
            'cascade_delete' => true,
            'reverse_relationship' => 'P53'
        ]
    ];

    protected $implicitModels = [
        /*[
            'relationship' => 'P4',
            'config' => [
                'key' => 'findDate',
                'name' => 'findDate',
                'value_node' => true,
                'cidoc_type' => 'E52'
            ]
        ],*/
    ];
}

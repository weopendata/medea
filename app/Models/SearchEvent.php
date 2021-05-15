<?php


namespace App\Models;


class SearchEvent extends Base
{
    public static $NODE_TYPE = 'E7';
    public static $NODE_NAME = 'searchEvent';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P14' => [
            'key' => 'person',
            'model_name' => 'Person',
            'cascade_delete' => false,
            'link_only' => true,
        ],
        'P7' => [
            'key' => 'searchArea',
            'model_name' => 'SearchArea',
            'cascade_delete' => false,
            'link_only' => true,
        ],
        'P20' => [
            'key' => 'findEvent',
            'model_name' => 'FindEvent',
            'cascade_delete' => false,
            'link_only' => true,
        ],
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

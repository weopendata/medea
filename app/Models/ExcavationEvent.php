<?php


namespace App\Models;


class ExcavationEvent extends Base
{
    public static $NODE_TYPE = 'A9';
    public static $NODE_NAME = 'excavationEvent';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P14' => [
            'key' => 'person',
            'model_name' => 'Person',
            'cascade_delete' => false,
            'link_only' => true,
            'reverse_relationship' => 'P14'
        ],
        'P70' => [
            'key' => 'publication',
            'model_name' => 'Publication',
            'cascade_delete' => false,
            'required' => false,
            'plural' => true,
        ],
        'AP3' => [
            'key' => 'searchArea',
            'model_name' => 'SearchArea',
            'cascade_delete' => false,
            'required' => false,
            'plural' => true,
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

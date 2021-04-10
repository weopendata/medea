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
            'link_only' => false, // These person objects are not persistent as they are not uniquely identified, i.e. only a name is provided
            'reverse_relationship' => 'P14'
        ],
        'P70' => [
            'key' => 'publication',
            'model_name' => 'Publication',
            'cascade_delete' => true,
            'required' => false,
            'plural' => true,
        ],
        'AP3' => [
            'key' => 'searchArea',
            'model_name' => 'SearchArea',
            'cascade_delete' => true,
            'required' => false,
            'plural' => false,
        ],
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P14',
            'config' => [
                'key' => 'company',
                'name' => 'company',
                'value_node' => true,
                'cidoc_type' => 'E47'
            ]
        ],
    ];
}

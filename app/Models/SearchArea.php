<?php


namespace App\Models;


use App\Services\NodeService;

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
            'reverse_relationship' => 'P7'
        ]
    ];

    protected $properties = [
        [
            'name' => 'internalId' // An ID used to uniquely identify the find without the internal Neo4J ID
        ]
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P1',
            'config' => [
                'key' => 'searchAreaTitle',
                'name' => 'searchAreaTitle',
                'value_node' => true,
                'cidoc_type' => 'E41'
            ]
        ],
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'searchAreaType',
                'name' => 'searchAreaType',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'searchAreaDescription',
                'name' => 'searchAreaDescription',
                'value_node' => true,
                'cidoc_type' => 'E62'
            ]
        ],
    ];

    /**
     * @param string $uniqueContextId The global unique context id, i.e. excavation ID + local context Id
     * @return string
     */
    public static function createInternalId(string $uniqueContextId)
    {
        return $uniqueContextId . '__SearchArea'; // There's only 1 search area per context
    }
}

<?php

namespace App\Models;

class ProductionClassification extends Base
{
    public static $NODE_TYPE = 'E17';

    public static $NODE_NAME = 'productionClassification';

    protected $hasUniqueId = false;

    protected $relatedModels = [
        'P108' => [
            'key' => 'publication',
            'model_name' => 'Publication',
            'cascade_delete' => true,
            'required' => false,
            'plural' => true,
            'reverse_relationship' => 'P67'
        ],
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P2',
            'config' => [
                'key' => 'productionClassificationType',
                'name' => 'productionClassificationType',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P3',
            'config' => [
                'key' => 'productionClassificationDescription',
                'name' => 'productionClassificationDescription',
                'value_node' => true,
                'cidoc_type' => 'E62'
            ]
        ],
        [
            'relationship' => 'P42',
            'config' => [
                'key' => 'productionClassificationPeriod',
                'name' => 'productionClassificationPeriod',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P42',
            'config' => [
                'key' => 'productionClassificationNation',
                'name' => 'productionClassificationNation',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ]
    ];

    protected $properties = [
        [
            'name' => 'agree',
            'default_value' => 0
        ],
        [
            'name' => 'disagree',
            'default_value' => 0
        ],
        [
            'name' => 'startDate'
        ],
        [
            'name' => 'endDate'
        ],
        [
            'name' => 'feedback'
        ]
    ];
}

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
            'cascade_delete' => false,
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
            'relationship' => 'P2',
            'config' => [
                'key' => 'productionClassificationMainType',
                'name' => 'productionClassificationMainType',
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
                'key' => 'productionClassificationCentury',
                'name' => 'productionClassificationCentury',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P42',
            'config' => [
                'key' => 'productionClassificationCulturePeople',
                'name' => 'productionClassificationCulturePeople',
                'value_node' => true,
                'cidoc_type' => 'E55'
            ]
        ],
        [
            'relationship' => 'P42',
            'config' => [
                'key' => 'productionClassificationRulerNation',
                'name' => 'productionClassificationRulerNation',
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
        ]
    ];
}

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

    protected $implicit_models = [
        [
            'relationship' => 'P33',
            'config' => [
                'key' => 'productionTechnique',
                'name' => 'productionTechnique',
                'cidoc_type' => 'E29',
                'plural' => false,
                'nested' => true
            ]
        ],
    ];

    public function createProductionTechnique($technique)
    {
        $client = $this->getClient();

        // Make E29 design or procedure
        $production_technique = $client->makeNode();
        $production_technique->setProperty('name', 'productionTechnique');
        $production_technique->save();
        $production_technique->addLabels(
            [
            self::makeLabel('E29'),
            self::makeLabel('productionTechnique'),
            self::makeLabel($this->getGeneralId())
            ]
        );

        // Make E55 productionType
        $production_type = $this->createValueNode('type', ['E55'], $technique['type']);
        $production_technique->relateTo($production_type, 'P2')->save();

        return $production_technique;
    }
}

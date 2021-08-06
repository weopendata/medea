<?php

namespace App\Models;

use App\Services\NodeService;

class ProductionEvent extends Base
{
    public static $NODE_TYPE = 'E12';
    public static $NODE_NAME = 'productionEvent';

    protected $relatedModels = [
        'P41' => [
            'key' => 'productionClassification',
            'model_name' => 'ProductionClassification',
            'cascade_delete' => true,
            'plural' => true
        ]
    ];

    protected $implicitModels = [
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
        // Make E29 design or procedure
        $production_technique = NodeService::makeNode();
        $production_technique->setProperty('name', 'productionTechnique');
        $production_technique->save();
        $production_technique->addLabels(
            [
            self::makeLabel('E29'),
            self::makeLabel('productionTechnique'),
            self::makeLabel($this->getGeneralId())
            ]
        );

        if (!empty($technique['productionTechniqueType'])) {
            // Make E55 productionType
            $production_type = $this->createValueNode('productionTechniqueType', ['E55', $this->getGeneralId()], $technique['productionTechniqueType']);
            $production_technique->relateTo($production_type, 'P2')->save();
        }


        if (!empty($technique['productionTechniqueSurfaceTreatmentType'])) {
            // Make E55 productionType
            $surface_treatment = $this->createValueNode('productionTechniqueSurfaceTreatmentType', ['E55', $this->getGeneralId()], $technique['productionTechniqueSurfaceTreatmentType']);
            $production_technique->relateTo($surface_treatment, 'P2')->save();
        }



        return $production_technique;
    }
}

<?php

namespace App\Models;

class FindEvent extends Base
{
    public static $NODE_TYPE = 'E10';
    public static $NODE_NAME = 'FindEvent';

    protected $relatedModels = [
        'P12' => [
            'key' => 'object',
            'model_name' => 'Object',
            'cascade_delete' => true
        ],
        'P7' => [
            'key' => 'findSpot',
            'model_name' => 'FindSpot',
            'cascade_delete' => true
        ]
    ];

    protected $implicitModels = [
        'P4' => [
            'key' => 'findDate',
            'object' => 'findPeriod',
            'value_node' => true,
            'cidoc_type' => 'E52'
        ]
    ];

    public function save()
    {
        parent::save();

        // Add an ID to the node
        $id_node = $id_node = $this->createValueNode(['E42', 'findId'], $this->node->getId());

        $this->node->relateTo($id_node, 'P1')->save();
    }
}

<?php

namespace App\Models;

class Photograph extends Base
{
    public static $NODE_TYPE = 'E38';
    public static $NODE_NAME = 'photograph';

    protected $related_models = [

    ];

    protected $implicit_models = [
        [
            'relationship' => 'P48',
            'config' => [
                'key' => 'name',
                'name' => 'photographId',
                'value_node' => false,
                'cidoc_type' => 'E42'
            ]
        ]
    ];

    protected $properties = [
        [
            'name' => 'resized',
        ],
        [
            'name' => 'width'
        ],
        [
            'name' => 'height'
        ]
    ];

    public function createPhotographId($photograph_name)
    {
        $client = $this->getClient();

        $identifier_node = $this->createValueNode('identifier', ['E42', 'photographId', $this->getGeneralId()], $photograph_name);

        return $identifier_node;
    }
}

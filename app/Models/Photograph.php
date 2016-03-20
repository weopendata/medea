<?php

namespace App\Models;

class Photograph extends Base
{
    public static $NODE_TYPE = 'E38';
    public static $NODE_NAME = 'Photograph';

    protected $relatedModels = [

    ];

    protected $implicitModels = [
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

    public function createPhotographId($photograph_name)
    {
        $client = $this->getClient();

        $identifier_node = $this->createValueNode('identifier', ['E42', 'photographId'], $photograph_name);

        return $identifier_node;
    }
}

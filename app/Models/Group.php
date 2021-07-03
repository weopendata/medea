<?php


namespace App\Models;

/**
 * Class Group
 *
 * @package App\Models
 */
class Group extends Base
{
    public static $NODE_TYPE = 'E74';
    public static $NODE_NAME = 'group';

    protected $hasUniqueId = true;

    protected $implicitModels = [
        [
            'relationship' => 'P131',
            'config' => [
                'key' => 'institutionName',
                'name' => 'institutionName',
                'value_node' => true,
                'cidoc_type' => 'E82'
            ]
        ],
        [
            'relationship' => 'P53',
            'config' => [
                'key' => 'institutionAddress',
                'name' => 'institutionAddress',
                'value_node' => true,
                'cidoc_type' => 'E53'
            ]
        ]
    ];

    /**
     * @param string $name
     * @return string
     */
    public static function createInternalId(string $name)
    {
        $name = strtolower($name);
        $name = trim($name);

        return $name . '__Group';
    }


    protected $properties = [
        [
            'name' => 'internalId' // An ID used to uniquely identify the find without the internal Neo4J ID
        ]
    ];
}

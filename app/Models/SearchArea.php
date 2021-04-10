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
        [
            'relationship' => 'P41',
            'config' => [
                'key' => 'searchAreaInterpretation',
                'name' => 'searchAreaInterpretation',
                'cidoc_type' => 'E17'
            ]
        ],
        [
            'relationship' => 'P140',
            'config' => [
                'key' => 'searchAreaPeriod',
                'name' => 'searchAreaPeriod',
                'cidoc_type' => 'E13'
            ]
        ],
    ];

    /**
     * @param $interpretation
     * @return \Everyman\Neo4j\Node|void
     * @throws \Everyman\Neo4j\Exception
     */
    public function createSearchAreaInterpretation($interpretation)
    {
        if (empty($interpretation['searchAreaInterpretation'])) {
            return;
        }

        $generalId = $this->getGeneralId();

        $typeAssignment = NodeService::makeNode();
        $typeAssignment->setProperty('name', 'searchAreaInterpretation');
        $typeAssignment->save();
        $typeAssignment->addLabels([
            self::makeLabel('E17'),
            self::makeLabel('searchAreaInterpretation'),
            self::makeLabel($generalId)
        ]);


        // Create an E55
        $type = $this->createValueNode(
            'searchAreaInterpretationType',
            ['E55', $generalId, 'searchAreaInterpretationType'],
            $interpretation['searchAreaInterpretation']
        );

        // Create the relationship
        $typeAssignment->relateTo($type, 'P42')->save();

        // Return the type
        return $typeAssignment;
    }

    public function createSearchAreaPeriod($period)
    {
        if (empty($period['searchAreaPeriod'])) {
            return;
        }

        $generalId = $this->getGeneralId();

        $periodAssignment = NodeService::makeNode();
        $periodAssignment->setProperty('name', 'searchAreaPeriod');
        $periodAssignment->save();
        $periodAssignment->addLabels([
            self::makeLabel('E13'),
            self::makeLabel('searchAreaPeriod'),
            self::makeLabel($generalId)
        ]);

        $periodNode = $this->createValueNode(
            'searchAreaPeriodValue',
            ['E4', $generalId, 'searchAreaPeriodValue'],
            $period['searchAreaPeriod']
        );

        // Create the relationship
        $periodAssignment->relateTo($periodNode, 'P140')->save();

        // Return the type
        return $periodAssignment;
    }
}

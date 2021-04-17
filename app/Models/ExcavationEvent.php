<?php


namespace App\Models;


use App\Services\NodeService;
use Everyman\Neo4j\Exception;

class ExcavationEvent extends Base
{
    public static $NODE_TYPE = 'A9';
    public static $NODE_NAME = 'excavationEvent';

    protected $hasUniqueId = true;

    protected $properties = [
        [
            'name' => 'internalId' // An ID used to uniquely identify the find without the internal Neo4J ID
        ]
    ];

    protected $relatedModels = [
        'P14' => [
            'key' => 'person',
            'model_name' => 'Person',
            'cascade_delete' => true,
            'link_only' => false, // These person objects are not persistent as they are not uniquely identified, i.e. only a name is provided
            'reverse_relationship' => 'P14'
        ],
        'P70' => [
            'key' => 'publication',
            'model_name' => 'Publication',
            'cascade_delete' => true,
            'required' => false,
            'plural' => true,
        ],
        'AP3' => [
            'key' => 'searchArea',
            'model_name' => 'SearchArea',
            'cascade_delete' => false,
            'link_only' => true,
            'required' => false,
        ],
    ];

    protected $implicitModels = [
        [
            'relationship' => 'P14',
            'config' => [
                'key' => 'company',
                'name' => 'company',
                'cidoc_type' => 'E74'
            ]
        ],
        [
            'relationship' => 'P33',
            'config' => [
                'key' => 'excavationProcedure',
                'name' => 'excavationProcedure',
                'cidoc_type' => 'E29'
            ]
        ],
    ];

    public function createCompany($company)
    {
        $generalId = $this->getGeneralId();

        $companyNode = NodeService::makeNode();
        $companyNode->setProperty('name', 'company');
        $companyNode->save();
        $companyNode->addLabels([
            self::makeLabel('E74'),
            self::makeLabel('company'),
            self::makeLabel($generalId)
        ]);

        $appellation = $this->createValueNode(
            'companyName',
            ['E41', $generalId, 'companyName'],
            $company['companyName']
        );

        // Create the relationship
        $companyNode->relateTo($appellation, 'P1')->save();

        return $companyNode;
    }

    public function createExcavationProcedure($excavationProcedure)
    {
        $generalId = $this->getGeneralId();

        $procedureNode = NodeService::makeNode();
        $procedureNode->setProperty('name', 'excavationProcedure');
        $procedureNode->save();
        $procedureNode->addLabels([
            self::makeLabel('E29'),
            self::makeLabel('excavationProcedure'),
            self::makeLabel($generalId)
        ]);

        $types = [
            'excavationProcedureMetalDetectionType',
            'excavationProcedureSiftingType',
            'excavationProcedureInventoryCompleteness'
        ];

        foreach ($types as $type) {
            $node = $this->createDetectionTypeNode($type, $excavationProcedure[$type], $generalId);

            if (!empty($node)) {
                $procedureNode->relateTo($node, 'P2')->save();
            }
        }

        return $procedureNode;
    }

    /**
     * @param $nodeName
     * @param $value
     * @param $generalId
     * @return \Everyman\Neo4j\Node
     */
    private function createDetectionTypeNode($nodeName, $value, $generalId)
    {
        try {
            return $this->createValueNode(
                $nodeName,
                ['E55', $generalId, $nodeName],
                $value
            );
        } catch (Exception $ex) {
            \Log::error($ex->getMessage());
            \Log::error($ex->getTraceAsString());
        }
    }
}

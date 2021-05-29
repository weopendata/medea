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
        'P70' => [
            'key' => 'publication',
            'model_name' => 'Publication',
            'cascade_delete' => false,
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
        'P12' => [
            'key' => 'collection',
            'model_name' => 'Collection',
            'link_only' => true, // TODO: this might need to change
            'required' => true
        ]
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
            'relationship' => 'P4',
            'config' => [
                'key' => 'excavationPeriod',
                'name' => 'excavationPeriod',
                'cidoc_type' => 'E52',
                'value_node' => true
            ]
        ],
        [
            'relationship' => 'P33',
            'config' => [
                'key' => 'excavationProcedureMetalDetection',
                'name' => 'excavationProcedureMetalDetection',
                'cidoc_type' => 'E29'
            ]
        ],
        [
            'relationship' => 'P33',
            'config' => [
                'key' => 'excavationProcedureSifting',
                'name' => 'excavationProcedureSifting',
                'cidoc_type' => 'E29'
            ]
        ],
        [
            // This person is a non persistent one, don't add it as a related model
            'relationship' => 'P14',
            'config' => [
                'key' => 'person',
                'name' => 'person',
                'cidoc_type' => 'E74'
            ]
        ],
    ];

    public function createPerson($person)
    {
        $generalId = $this->getGeneralId();

        $personNode = NodeService::makeNode();
        $personNode->setProperty('name', 'person');
        $personNode->save();
        $personNode->addLabels([
            self::makeLabel('E74'),
            self::makeLabel('person'),
            self::makeLabel($generalId)
        ]);

        $fullName = $this->createValueNode(
            'firstName',
            ['E82', $generalId, 'firstName'],
            $person['firstName']
        );

        // Create the relationship
        $personNode->relateTo($fullName, 'P131')->save();

        return $personNode;
    }

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

    /**
     * @param array $data
     * @return \Everyman\Neo4j\Node|void
     * @throws Exception
     */
    public function createExcavationProcedureMetalDetection($value)
    {
        $generalId = $this->getGeneralId();

        $procedureNode = NodeService::makeNode();
        $procedureNode->setProperty('name', 'excavationProcedureMetalDetection');
        $procedureNode->save();
        $procedureNode->addLabels([
            self::makeLabel('E29'),
            self::makeLabel('excavationProcedureMetalDetection'),
            self::makeLabel($generalId)
        ]);

        $procedureType = $this->createValueNode('excavationProcedureMetalDetectionType', ['E55', 'excavationProcedureType'], $value);

        $procedureNode->relateTo($procedureType, 'P2')->save();

        return $procedureNode;
    }

    /**
     * @param array $data
     * @return \Everyman\Neo4j\Node|void
     * @throws Exception
     */
    public function createExcavationProcedureSifting($value)
    {
        $generalId = $this->getGeneralId();

        $procedureNode = NodeService::makeNode();
        $procedureNode->setProperty('name', 'excavationProcedureSifting');
        $procedureNode->save();
        $procedureNode->addLabels([
            self::makeLabel('E29'),
            self::makeLabel('excavationProcedureSifting'),
            self::makeLabel($generalId)
        ]);

        $procedureType = $this->createValueNode('excavationProcedureSiftingType', ['E55', 'excavationProcedureType'], $value);

        $procedureNode->relateTo($procedureType, 'P2')->save();

        return $procedureNode;
    }

    /*public function createExcavationProcedure($excavationProcedure)
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
    }*/

    /**
     * @param $nodeName
     * @param $value
     * @param $generalId
     * @return \Everyman\Neo4j\Node
     */
    /*private function createDetectionTypeNode($nodeName, $value, $generalId)
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
    }*/
}

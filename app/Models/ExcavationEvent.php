<?php


namespace App\Models;


use App\Services\NodeService;

class ExcavationEvent extends Base
{
    public static $NODE_TYPE = 'A9';
    public static $NODE_NAME = 'excavationEvent';

    protected $hasUniqueId = true;

    protected $relatedModels = [
        'P14' => [
            'key' => 'person',
            'model_name' => 'Person',
            'cascade_delete' => false,
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
            'cascade_delete' => true,
            'required' => false,
            'plural' => false,
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
}

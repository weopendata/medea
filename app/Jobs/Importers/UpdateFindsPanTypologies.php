<?php

namespace App\Jobs\Importers;

use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;

class UpdateFindsPanTypologies extends AbstractImporter
{
    /**
     * @param  array $data
     * @param  int   $index
     * @return void
     */
    public function processData(array $data, int $index)
    {
        try {
            $findId = $data['identifier'];
            $panId = $data['PANid'];
            $classificationDescription = @$data['classificationDescription'];

            if (empty($panId)) {
                throw new \Exception("No PAN ID was specified, skipping the update for this line.");
            }

            $find = app(FindRepository::class)->getById($findId);

            if (empty($find)) {
                throw new \Exception("No find found with ID $findId");
            }

            $objectId = app(FindRepository::class)->getRelatedObjectId($findId);

            if (empty($objectId)) {
                throw new \Exception("No object node found, attached to the find with identifier $findId");
            }

            $productionClassification = [
                'productionClassificationValue' => $panId,
                'productionClassificationType' => 'Typologie',
                'productionClassificationDescription' => $classificationDescription,
            ];

            app(ObjectRepository::class)->updatePanTypologyClassification($objectId, $productionClassification);

            $this->addLog($index, "Updated a find, set pan ID to $panId for find with node id $findId", 'update', ['identifier' => $findId, 'data' => $data], true);
        } catch (\Exception $ex) {
            $this->addLog($index, 'Something went wrong: ' . $ex->getMessage(), 'update', ['data' => $data], false);
        }
    }
}
<?php

namespace App\Jobs\Importers;

use App\Repositories\ContextRepository;
use App\Repositories\ExcavationRepository;

class ImportExcavations extends AbstractImporter
{
    /**
     * @param array $data
     * @param int $index
     * @return void
     */
    public function processData(array $data, int $index)
    {
        $isValid = $this->validate($data, $index);

        if (!$isValid) {
            return;
        }

        $existingExcavation = app(ExcavationRepository::class)->getByInternalId($data['excavationUUID']);

        $action = !empty($existingExcavation) ? 'update' : 'create';

        try {
            $excavationModel = $this->createExcavationModel($data);

            if ($action == 'update') {
                $excavationId = $existingExcavation->getId();

                app(ExcavationRepository::class)->update($excavationId, $excavationModel);

                $this->addLog($index, 'Updated an excavation ', $action, ['identifier' => $excavationId, 'data' => $data], true);
            } else {
                $excavationId = app(ExcavationRepository::class)->store($excavationModel);

                $this->addLog($index, 'Added an excavation ', $action, ['identifier' => $excavationId, 'data' => $data], true);
            }
        } catch (\Exception $ex) {
            $this->addLog($index, 'Something went wrong: ' . $ex->getMessage(), $action, ['data' => $data], false);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function createExcavationModel(array $data)
    {
        $excavation = [];

        $mapping = [
            'title' => 'excavationTitle',
            'excavationUUID' => 'excavationUUID',
            'excavationUUIDType' => 'excavationUUIDType',
            'excavationCustomNumber' => 'excavationCustomNumber',
            'period' => 'excavationPeriod',
            /*'metalDetectionUsed' => 'metalDetectionUsed',
            'siftingUsed' => 'siftingUsed',*/
            //'inventoryCompleteness' => 'inventoryCompleteness', // Not yet clear how these are mapped
            //'remarks' => 'remarks', // Not yet clear how these are mapped
            /*'depotName' => 'depotName',
            'depotAddress' => 'depotAddress',*/ // Not yet clear how these are mapped
        ];

        foreach ($mapping as $key => $field) {
            $excavation[$field] = array_get($data, $key);
        }

        $excavation['internalId'] = $excavation['excavationUUID'];
        $excavation['company'] = ['companyName' => $data['company']];
        /*$excavation['excavationProcedure'] = ['excavationProcedureType' => [
            'metalDetection'
        ]];*/

        // Add the Person link
        $excavation['person'] = [
            'firstName' => $data['projectManager'],
        ];

        // Add the Publication link
        $excavation['publication'] = [
            [
                'publicationTitle' => array_get($data, 'reportTitle'),
                'publicationCreation' => [
                    'publicationCreationActor' => [
                        [
                            'publicationCreationActorName' => array_get($data, 'reportPublisher'),
                            'publicationCreationActorType' => 'publisher'
                        ]
                    ],
                    'publicationCreationLocation' => [
                        'publicationCreationLocationAppellation' => array_get($data, 'reportPlace'),
                    ],
                    'publicationCreationTimeSpan' => [
                        'date' => array_get($data, 'reportDate'),
                    ]
                ]
            ]
        ];

        if (!empty($data['reportResearchURI'])) {
            $excavation['publication'][] = [
                'uri' => $data['reportResearchURI']
            ];
        }

        if (!empty($data['reportArchiveURI'])) {
            $excavation['publication'][] = [
                'uri' => $data['reportArchiveURI']
            ];
        }

        // Add the search area link
        $searchAreaFields = [
            'searchAreaInterpretation',
            'searchAreaPeriod',
            'searchAreaDescription',
            'searchAreaTitle',
        ];

        $searchArea = [];

        foreach ($searchAreaFields as $searchAreaField) {
            if (empty($data[$searchAreaField])) {
                continue;
            }

            $searchArea[$searchAreaField] = $data[$searchAreaField];
        }

        // Add the location
        $searchArea['location'] = [];

        //searchAreaLocationAddress	searchAreaName	searchAreaLatitude	searchAreaLongitude
        if ($this->hasAddressData($data)) {
            $address = [];
            $address['locationAddressStreet'] = @$data['searchAreaStreet'];
            $address['locationAddressNumber'] = @$data['searchAreaNumber'];
            $address['locationAddressPostalCode'] = @$data['searchAreaPostalCode'];
            $address['locationAddressLocality'] = @$data['searchAreaLocality'];

            $searchArea['location']['address'] = $address;
        }

        if (!empty($data['searchAreaName'])) {
            $searchArea['location']['locationPlaceName'] = [
                'appellation' => $data['searchAreaName']
            ];
        }

        if (!empty($data['searchAreaLatitude']) && !empty($data['searchAreaLongitude'])) {
            $searchArea['location']['lng'] = $data['searchAreaLongitude'];
            $searchArea['location']['lat'] = $data['searchAreaLatitude'];
        }

        $excavation['searchArea'] = $searchArea;

        // Return the excavation tree
        return $excavation;
    }

    /**
     * Validate and add a log if necessary
     * @param array $data
     * @param int $index
     * @return bool
     */
    private function validate(array $data, int $index)
    {
        try {
            $action = !empty($data['MEDEA_ID']) ? 'update' : 'create';

            $this->containsAllRequiredInformation($data);
        } catch (\Exception $ex) {
            $this->addLog($index, 'Some required information is missing: ' . $ex->getMessage(), $action, ['data' => $data], false);

            return false;
        }

        if (!$this->containsSearchAreaInformation($data)) {
            $this->addLog($index,
                'Some required information is missing, make sure searchAreaLocationAddress or searchAreaName or searchAreaLatitude and searchAreaLongitude is filled in.', $action,
                ['data' => $data], false);

            return false;
        }

        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function containsSearchAreaInformation(array $data)
    {
        return (!empty($data['searchAreaPostalCode']) && !empty($data['searchAreaLocality']))
            || !empty($data['searchAreaName'])
            || (!empty($data['searchAreaLatitude']) && !empty($data['searchAreaLongitude']));
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    private function containsAllRequiredInformation(array $data)
    {
        foreach ($this->getRequiredFields() as $field) {
            $value = @$data[$field] ?? '';
            $value = trim($value);

            if (empty($value)) {
                throw new \Exception("The field $field is required but contained an empty value.");
            }
        }

        return true;
    }

    private function getRequiredFields()
    {
        return [
            "excavationUUID",
            "excavationUUIDType",
            "title",
            "company",
            "period",
            "searchAreaPeriod",
            "metalDetectionUsed",
            "siftingUsed",
            "depotName",
        ];
    }

    /**
     * @param array $data
     * @return bool
     */
    private function hasAddressData(array $data)
    {
        return !empty($data['searchAreaPostalCode'])
            && !empty($data['searchAreaLocality']);
    }
}

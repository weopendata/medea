<?php

namespace App\Jobs\Importers;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Group;
use App\Models\SearchArea;
use App\Repositories\CollectionRepository;
use App\Repositories\ExcavationRepository;
use App\Repositories\GroupRepository;
use App\Repositories\SearchAreaRepository;

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

        $existingExcavation = app(ExcavationRepository::class)->getByInternalId($data['excavationID']);

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
            $this->addLog($index, 'Something went wrong: ' . $ex->getMessage(), $action, ['data' => $data, 'trace' => $ex->getTraceAsString()], false);

            \Log::error($ex->getTraceAsString());
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function createExcavationModel(array $data): array
    {
        $excavation = [];

        $mapping = [
            'excavationTitle' => 'excavationTitle',
            'excavationID' => 'excavationID',
            'excavationIDType' => 'excavationIDType',
            'excavationCustomNumber' => 'excavationCustomNumber',
            'excavationPeriod' => 'excavationPeriod',
            'remarks' => 'remarks',
            'depotName' => 'depotName',
            'depotAddress' => 'depotAddress',
            'depotId' => 'depotId'
        ];

        foreach ($mapping as $key => $field) {
            $excavation[$field] = array_get($data, $key);
        }

        // Get or create the persistent excavation collection
        $collectionInternalId = Collection::createInternalId($excavation['excavationID']);

        $collection = app(CollectionRepository::class)->getByInternalId($collectionInternalId);

        if (empty($collection)) {
            $collection = [
                'internalId' => $collectionInternalId,
                'collectionType' => 'opgravingsarchief'
            ];

            $collection = app(CollectionRepository::class)->store($collection);
        }

        // Get or create the group based on depotName and attach it to the collection
        $depotId = $excavation['depotId'];
        $depotName = $excavation['depotName'];
        $depotAddress = $excavation['depotAddress'];

        unset($excavation['depotName']);
        unset($excavation['depotAddress']);
        unset($excavation['depotId']);

        $group = [
            'internalId' => $depotId,
            'depotId' => $depotId,
            'institutionName' => $depotName,
            'institutionAddress' => $depotAddress
        ];

        $groupId = app(GroupRepository::class)->findOrCreate($group);

        app(CollectionRepository::class)->linkWithGroup($collection->getId(), $groupId);

        $excavation['collection'] = [
            'id' => $collection->getId()
        ];

        // Map the metal and sifting methods
        $metalDetectionValue = $this->parseMetalDetectionValue($data['metalDetectionUsed']);
        $siftingTypeValue = $this->parseSiftingTypeValue($data['siftingUsed']);
        $inventoryCompletenessValue = $this->parseInventoryCompleteness($data['inventoryCompleteness']);

        $excavation['internalId'] = $excavation['excavationID'];
        $excavation['company'] = ['companyName' => $data['excavationCompany']];
        $excavation['excavationProcedureSifting'] = $siftingTypeValue;
        $excavation['excavationProcedureMetalDetection'] = $metalDetectionValue;
        $excavation['inventoryCompleteness'] = $inventoryCompletenessValue;

        // Add the Person link
        $excavation['person'] = [
            'firstName' => $data['projectManager'],
        ];

        // Add the Publication link
        $excavation['publication'] = [
            [
                'publicationResearchURI' => array_get($data, 'reportResearchURI'),
                'publicationArchiveURI' => array_get($data, 'reportArchiveURI'),
                'publicationTitle' => array_get($data, 'reportTitle'),
                'publicationContact' => array_get($data, 'reportAuthor'),
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

        // Add the search area link
        $searchAreaFields = [
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

        $searchAreaId = $this->updateLinkSearchArea($excavation['internalId'], $searchArea);

        if (!empty($searchAreaId)) {
            $excavation['searchArea'] = ['id' => $searchAreaId];
        }

        // Return the excavation tree
        return $excavation;
    }

    /**
     * @param string $excavationId
     * @param array $searchAreaData
     * @return int|void
     */
    private function updateLinkSearchArea($excavationId, array $searchAreaData)
    {
        // The linked search area can be found by fetching the linked "based context" C0 of the excavation
        // This Context object is identified by concatenating the excavation ID + C0
        // The linked SearchArea's internal id is based on this unique context ID and will always be the one linked to this excavation
        $contextInternalId = Context::createInternalId('C0', $excavationId);
        $searchAreaInternalId = SearchArea::createInternalId($contextInternalId);

        $searchArea = app(SearchAreaRepository::class)->getByInternalId($searchAreaInternalId);

        if (!empty($searchArea)) {
            $searchAreaNodeId = $searchArea->getId();
        } else {
            $searchAreaNodeId = app(SearchAreaRepository::class)->store($searchAreaData);
        }

        $searchAreaData['internalId'] = $searchAreaInternalId;

        // Update the search Area
        app(SearchAreaRepository::class)->update($searchAreaNodeId, $searchAreaData);

        return $searchAreaNodeId;
    }

    private function parseMetalDetectionValue($value)
    {
        $value = strtolower($value);

        switch ($value) {
            case 'ja':
                return 'metaaldetectie';
            case 'nee':
                return 'selectief of geen metaaldetectie';
            default:
                return $value;
        }
    }

    private function parseSiftingTypeValue($value)
    {
        $value = strtolower($value);

        switch ($value) {
            case 'ja':
                return 'systematisch gezeefd';
            case 'nee':
                return 'selectief of niet gezeefd';
            default:
                return $value;
        }
    }

    /**
     * @param string $value
     * @return string
     */
    private function parseInventoryCompleteness($value)
    {
        $value = strtolower($value);

        switch ($value) {
            case 'ja':
                return 'volledig geïnventariseerd';
            case 'nee':
                return 'selectief geïnventariseerd';
            default:
                return $value;
        }
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
            'excavationID',
            'excavationIDType',
            'excavationTitle',
            'excavationCompany',
            'excavationPeriod',
            'searchAreaPeriod',
            'metalDetectionUsed',
            'siftingUsed',
            'depotId',
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
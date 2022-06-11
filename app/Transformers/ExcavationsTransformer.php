<?php

namespace App\Transformers;

class ExcavationsTransformer extends Transformer
{
    /**
     * @param  array excavations
     * @return array
     */
    public function transform(array $excavations): array
    {
        return collect($excavations)
            ->map(function ($excavation) {
                return $this->transformOutgoingExcavation($excavation);
            })
            ->toArray();
    }

    /**
     * @param  array $excavation
     * @return array
     */
    private function transformOutgoingExcavation(array $excavation): array
    {
        $propertyMapping = [
            'excavationID' => 'excavationID',
            'excavationIDType' => 'excavationIDType',
            'excavationTitle' => 'excavationTitle',
            'excavationPeriod' => 'excavationPeriod',
            'projectManager' => 'person.firstName',
            'excavationCompany' => 'company.companyName',
            'depotId' => 'collection.group.depotId',
            'depotName' => 'collection.group.institutionName',
            'depotAddress' => 'collection.group.institutionAddress',
            'siftingUsed' => 'excavationProcedureSifting.excavationProcedureSiftingType',
            'metalDetectionUsed' => 'excavationProcedureMetalDetection.excavationProcedureMetalDetectionType',
            'reportResearchURI' => 'publication.0.reportResearchURI',
            'reportArchiveURI' => 'publication.0.publicationArchiveURI',
            'reportTitle' => 'publication.0.publicationTitle',
            'reportPublisher' => 'publication.0.publicationCreation.0.publicationCreationActor.publicationCreationActorName',
            'reportPlace' => 'publication.0.publicationCreation.0.publicationCreationLocation.publicationCreationLocationAppellation',
            'reportDate' => 'publication.0.publicationCreation.0.publicationCreationTimeSpan.date',
            'searchAreaDescription' => 'searchArea.searchAreaDescription',
            'searchAreaStreet' => 'searchArea.location.address.locationAddressStreet',
            'searchAreaNumber' => 'searchArea.location.address.locationAddressNumber',
            'searchAreaPostalCode' => 'searchArea.location.address.locationAddressPostalCode',
            'searchAreaLocality' => 'searchArea.location.address.locationAddressLocality',
            'searchAreaName' => 'searchArea.location.locationPlaceName.appellation',
            'searchAreaLongitude' => 'searchArea.location.lng',
            'searchAreaLatitude' => 'searchArea.location.lat',
        ];

        $transformedExcavation = [];

        foreach ($propertyMapping as $key => $path) {
            $transformedExcavation[$key] = array_get($excavation, $path) ?? '';
        }

        return $transformedExcavation;
    }
}
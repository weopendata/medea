<?php

namespace App\Services;

use App\Repositories\ElasticSearch\FindRepository;
use App\Repositories\Eloquent\PanTypologyRepository;
use App\Repositories\ExcavationRepository;

class IndexingService
{
    /**
     * @param  int $findId The Neo4J ID of a FindEvent
     * @return void
     * @throws \Exception
     */
    public function indexFind(int $findId)
    {
        $elasticSearchId = null;

        $findDocument = app(FindRepository::class)->getByNeo4jId($findId);

        if (!empty($findDocument)) {
            $elasticSearchId = $findDocument['id'];
        }

        $find = app(\App\Repositories\FindRepository::class)->expandValues($findId);

        if (empty($find)) {
            throw new \Exception("Node with id $findId could not be found.");
        }

        $find = $this->transformFind($find);

        if (empty($elasticSearchId)) {
            $elasticSearchId = app(FindRepository::class)->store($find);

            if (empty($elasticSearchId)) {
                throw new \Exception("Something went wrong while indexing find with ID " . $find['identifier']);
            }

            return;
        }

        app(FindRepository::class)->update($elasticSearchId, $find);
    }

    /**
     * @param  array $find
     * @return array
     */
    private function transformFind(array $find): array
    {
        // Get the related excavation information
        $excavation = [];
        $excavationId = array_get($find, 'excavationId');

        if (!empty($excavationId)) {
            $excavation = app(ExcavationRepository::class)->getDataViaInternalId($excavationId);
        }

        $object = $find['object'];

        $width = $this->fetchDimensionFromObject($object, 'breedte');
        $height = $this->fetchDimensionFromObject($object, 'hoogte');
        $depth = $this->fetchDimensionFromObject($object, 'diepte');
        $diameter = $this->fetchDimensionFromObject($object, 'diameter');
        $weight = $this->fetchDimensionFromObject($object, 'gewicht');

        $complete = $this->fetchDistinguishingFeature($object, 'volledigheid');
        $conservation = $this->fetchDistinguishingFeature($object, 'conservering');
        $insignia = $this->fetchDistinguishingFeature($object, 'opschrift');
        $mark = $this->fetchDistinguishingFeature($object, 'merkteken');

        $panTypology = $this->fetchPanTypology($object);

        // Transform the find into a structure that the ElasticSearch repository understands
        return [
            'findId' => $find['identifier'],
            'findUUID' => @$find['internalId'],
            'excavationId' => @$find['excavationId'],
            'contextId' => @$find['contextId'],
            'finderId' => array_get($find, 'person.identifier'),
            'finderEmail' => array_get($find, 'person.email'),
            'findDate' => array_get($find, 'findDate'),
            'objectNr' => array_get($object, 'objectNr'),
            'objectCategory' => array_get($object, 'objectCategory'),
            'objectPeriod' => array_get($object, 'period'),
            'objectMaterial' => array_get($object, 'objectMaterial'),
            'objectDescription' => array_get($object, 'objectDescription'),
            'amount' => array_get($object, 'objectNumberOfParts'),
            'validation' => array_get($object, 'objectValidationStatus'),
            'objectTechnique' => array_get($object, 'productionEvent.productionTechnique.productionTechniqueType'),
            'modification' => array_get($object, 'treatmentEvent.modificationTechnique.modificationTechniqueType'),
            'treatment' => array_get($object, 'productionEvent.productionTechnique.productionTechniqueSurfaceTreatmentType'),
            'width' => array_get($width, 'measurementValue'),
            'widthUnit' => array_get($width, 'dimensionUnit'),
            'height' => array_get($height, 'measurementValue'),
            'heightUnit' => array_get($height, 'dimensionUnit'),
            'depth' => array_get($depth, 'measurementValue'),
            'depthUnit' => array_get($depth, 'dimensionUnit'),
            'diameter' => array_get($diameter, 'measurementValue'),
            'diameterUnit' => array_get($diameter, 'dimensionUnit'),
            'weight' => array_get($weight, 'dimensionUnit'),
            'weightUnit' => array_get($weight, 'dimensionUnit'),
            'findSpotLocality' => array_get($find, 'findSpot.location.address.locationAddressLocality'),
            'lat' => array_get($find, 'findSpot.location.lat'),
            'lon' => array_get($find, 'findSpot.location.lng'),
            'accuracy' => array_get($find, 'findSpot.location.accuracy'),
            'excavationTitle' => array_get($excavation, 'excavationTitle'),
            'excavationLocality' => array_get($excavation, 'searchArea.location.address.locationAddressLocality'),
            'excavationLat' => array_get($excavation, 'searchArea.location.lat'),
            'excavationLng' => array_get($excavation, 'searchArea.location.lng'),
            'panId' => array_get($panTypology, 'panId'),
            'panInitialPeriod' => array_get($panTypology, 'startYear'),
            'panFinalPeriod' => array_get($panTypology, 'endYear'),
            'conservation' => in_array(strtolower(array_get($conservation, 'distinguishingFeatureNote') ?? ''), ["nee", "neen", "onbekend"]) ? 'nee' : 'ja',
            'complete' => in_array(strtolower(array_get($complete, 'distinguishingFeatureNote') ?? ''), ["nee", "neen", "onbekend"]) ? 'nee' : 'ja',
            'mark' => in_array(strtolower(array_get($mark, 'distinguishingFeatureNote') ?? ''), ["nee", "neen", "onbekend"]) ? 'nee' : 'ja',
            'inscription' => in_array(strtolower(array_get($insignia, 'distinguishingFeatureNote') ?? ''), ["nee", "neen", "onbekend"]) ? 'nee' : 'ja',
            'photographPath' => array_get($object, 'photograph.0.src'),
            'photographCaption' => array_get($object, 'photograph.0.photographCaption'),
            'photographNote' => array_get($object, 'photograph.0.photographNote'),
            'photographAttribution' => array_get($object, 'photograph.0.photographRights.photographRightsAttribution'),
            'photographLicense' => array_get($object, 'photograph.0.photographRights.photographRightsLicense'),
            'photographCaptionPresent' => empty(array_get($object, 'photograph.photographCaption')) ? 'nee' : 'ja',
            'embargo' => array_get($object, 'embargo'),
            'collection' => array_get($find, 'collection.title'),
        ];
    }

    /**
     * @param  array  $object
     * @param  string $dimensionType
     * @return array
     */
    private function fetchDimensionFromObject(array $object, string $dimensionType): array
    {
        $dimensionInfo = collect(@$object['dimensions'] ?? [])
            ->filter(function ($dimension) use ($dimensionType) {
                return $dimension['dimensionType'] == $dimensionType;
            })
            ->first();

        if (empty($dimensionInfo)) {
            return [];
        }

        return $dimensionInfo;
    }

    /**
     * @param  array  $object
     * @param  string $featureType
     * @return array
     */
    private function fetchDistinguishingFeature(array $object, string $featureType): array
    {
        $feature = collect(@$object['distinguishingFeatures'] ?? [])
            ->filter(function ($dimension) use ($featureType) {
                return $dimension['distinguishingFeatureType'] == $featureType;
            })
            ->first();

        if (empty($feature)) {
            return [];
        }

        return $feature;
    }

    /**
     * @param  array $object
     * @return void
     */
    private function fetchPanTypology(array $object): array
    {
        $panTypology = collect(array_get($object, 'productionEvent.productionClassification') ?? [])
            ->filter(function ($dimension) {
                return @$dimension['productionClassificationType'] == 'Typologie';
            })
            ->first();

        if (empty($panTypology)) {
            return [];
        }

        $panId = $panTypology['productionClassificationValue'];

        if (empty($panId)) {
            return [];
        }

        $information = app(PanTypologyRepository::class)->getPanTypologyInformationForIds([$panId]);
        $information = @$information[$panId] ?? [];

        return [
            'panId' => $panId,
            'startYear' => array_get($information, 'startYear'),
            'endYear' => array_get($information, 'endYear'),
        ];
    }

}
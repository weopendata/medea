<?php

namespace App\Transformers;

use App\Repositories\ElasticSearch\FindRepository;

class FindFacetsTransformer extends Transformer
{
    /**
     * @param  array $findFacets
     * @return array
     */
    public function transform(array $findFacets): array
    {
        $propertyMapping = [
            'collection' => 'collectionTitle',
            'objectPeriod' => 'period',
            'objectCategory' => 'category',
            'objectMaterial' => 'material',
            'objectTechnique' => 'technique',
            'inscription' => 'insignia',
        ];

        $presenceFacetProperties = FindRepository::PRESENCE_FACET_PROPERTIES;

        $excludedFacets = explode(',', env('EXCLUDED_FILTER_FACETS')) ?? [];

        $facetsResult = [];

        collect($findFacets)
            ->filter(function ($facetValue, $facetKey) use ($excludedFacets) {
                return !in_array($facetKey, $excludedFacets);
            })
            ->each(function ($facetValue, $facetKey) use ($presenceFacetProperties, $propertyMapping, &$facetsResult) {
                $outgoingFacetKey = @$propertyMapping[$facetKey] ?? $facetKey;

                $facetsResult[$outgoingFacetKey] = array_keys($facetValue);

                if (!in_array($outgoingFacetKey, $presenceFacetProperties)) {
                    return;
                }

                // The presence facet is a facet where the only useful value is "Ja", meaning if we have values
                // outside of "nee", then we can add the "Ja" facet as a value
                $facetValueKeys = array_keys($facetValue);
                $validValueKeyPresent = collect($facetValueKeys)
                    ->filter(function ($facetValueKey) {
                        return strtolower($facetValueKey) !== 'nee';
                    })
                    ->values()
                    ->count() > 0;

                if (!$validValueKeyPresent) {
                    unset($facetsResult[$outgoingFacetKey]);

                    return;
                }

                $facetsResult[$outgoingFacetKey] = [
                    'Aanwezig'
                ];
            });

        return $facetsResult;
    }
}
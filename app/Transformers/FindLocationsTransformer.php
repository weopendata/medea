<?php

namespace App\Transformers;

class FindLocationsTransformer extends Transformer
{
    /**
     * @param  array $findLocations
     * @return array
     */
    public function transform(array $findLocations): array
    {
        return [
            'markers' => collect($findLocations['markers'])
                ->map(function ($findLocation) {
                    return [
                        'identifier' => $findLocation['findId'],
                        'location' => $findLocation['location'],
                    ];
                })
                ->toArray(),
            'total' => $findLocations['total']
        ];
    }
}
<?php

namespace App\Transformers;

use Sk\Geohash\Geohash;

class FindsHeatMapTransformer extends Transformer
{
    /**
     * @param  array $findsHeatMap
     * @return array
     */
    public function transform(array $findsHeatMap): array
    {
        return collect($findsHeatMap)
            ->map(function ($heatMapResult) {
                $geoHash = $heatMapResult['key'];

                $g = new Geohash();
                $coordinates = $g->decode($geoHash);

                return [
                    'count' => $heatMapResult['doc_count'],
                    'centre' => [
                        'lat' => $coordinates[0],
                        'lon' => $coordinates[1]
                    ]
                ];
            })
            ->toArray();
    }
}
<?php

namespace App\Services;

use App\Transformers\ContextsTransformer;
use App\Transformers\ExcavationsTransformer;
use App\Transformers\FindFacetsTransformer;
use App\Transformers\FindLocationsTransformer;
use App\Transformers\FindsHeatMapTransformer;
use App\Transformers\FindsTransformer;
use App\Transformers\Transformer;

class TransformerService
{
    /**
     * @param  array $finds
     * @return array
     */
    public static function transformFinds(array $finds): array
    {
        return self::transform($finds, new FindsTransformer);
    }

    /**
     * @param  array $findFacets
     * @return array
     */
    public static function transformFindFacets(array $findFacets): array
    {
        return self::transform($findFacets, new FindFacetsTransformer);
    }

    /**
     * @param  array $findLocations
     * @return array
     */
    public static function transformFindLocations(array $findLocations): array
    {
        return self::transform($findLocations, new FindLocationsTransformer);
    }

    /**
     * @param  array $findsHeatMap
     * @return array
     */
    public static function transformFindsHeatMap(array $findsHeatMap): array
    {
        return self::transform($findsHeatMap, new FindsHeatMapTransformer());
    }

    /**
     * @param  array $excavations
     * @return array
     */
    public static function transformExcavations(array $excavations): array
    {
        return self::transform($excavations, new ExcavationsTransformer);
    }

    /**
     * @param  array $contexts
     * @return array
     */
    public static function transformContexts(array $contexts): array
    {
        return self::transform($contexts, new ContextsTransformer);
    }

    /**
     * @param  array       $objects
     * @param  Transformer $transformer
     * @return array
     */
    private static function transform(array $objects, Transformer $transformer): array
    {
        return $transformer->transform($objects);
    }
}
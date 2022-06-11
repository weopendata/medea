<?php

namespace App\Services;

use App\Repositories\ExcavationRepository;

class ExcavationService
{
    /**
     * @param  int $limit
     * @param  int $offset
     * @return void
     */
    public function getAll(int $limit = 20, int $offset = 0)
    {
        $excavationNodes = app(ExcavationRepository::class)->getAllNodes($limit, $offset);

        $excavations = [];

        foreach ($excavationNodes as $excavationNode) {
            $excavation = app(ExcavationRepository::class)->expandValues($excavationNode->getId());

            $excavations[] = $excavation;
        }

        return app(TransformerService::class)->transformExcavations($excavations);
    }
}
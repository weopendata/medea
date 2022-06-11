<?php

namespace App\Services;

use App\Repositories\ContextRepository;
use App\Repositories\ExcavationRepository;

class ContextService
{
    /**
     * @param  int $limit
     * @param  int $offset
     * @return void
     */
    public function getAll(int $limit = 20, int $offset = 0)
    {
        $excavationNodes = app(ContextRepository::class)->getAllNodes($limit, $offset);

        $contexts = [];

        foreach ($excavationNodes as $excavationNode) {
            $excavation = app(ContextRepository::class)->expandValues($excavationNode->getId());

            $contexts[] = $excavation;
        }

        return app(TransformerService::class)->transformContexts($contexts);
    }
}
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
        $contextNodes = app(ContextRepository::class)->getAllNodes($limit, $offset);

        $contexts = [];

        foreach ($contextNodes as $contextNode) {
            $context = app(ContextRepository::class)->expandValues($contextNode->getId());

            $context['relatedContext'] = app(ContextRepository::class)->getRelatedContextId($contextNode->getId());

            $contexts[] = $context;
        }

        return app(TransformerService::class)->transformContexts($contexts);
    }
}
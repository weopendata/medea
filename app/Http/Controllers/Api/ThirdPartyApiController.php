<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyRequest;
use App\Services\ContextService;
use App\Services\ExcavationService;
use App\Services\FindService;
use Illuminate\Http\JsonResponse;

class ThirdPartyApiController extends Controller
{
    /**
     * @param  ThirdPartyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinds(ThirdPartyRequest $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $offset = $request->input('offset', 0);

        $results = app(FindService::class)->getAllWithFilter([], $limit, $offset, 'findDate', 'ASC', false);
        $finds = $results['data'];

        return response()->json($finds);
    }

    /**
     * @param  ThirdPartyRequest $request
     * @return JsonResponse
     */
    public function getExcavations(ThirdPartyRequest $request): JsonResponse
    {
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        return response()->json(app(ExcavationService::class)->getAll($limit, $offset));
    }

    /**
     * @param  ThirdPartyRequest $request
     * @return JsonResponse
     */
    public function getContexts(ThirdPartyRequest $request): JsonResponse
    {
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        return response()->json(app(ContextService::class)->getAll($limit, $offset));
    }
}
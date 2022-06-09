<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyRequest;
use App\Repositories\ElasticSearch\FindRepository;

class ThirdPartyFindController extends Controller
{
    /**
     * @param  ThirdPartyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleThirdPartyRequest(ThirdPartyRequest $request): \Illuminate\Http\JsonResponse
    {
        $limit = $request->input('limit', 100);
        $offset = $request->input('offset', 0);

        return response()->json(app(FindRepository::class)->getAll($limit, $offset));
    }
}
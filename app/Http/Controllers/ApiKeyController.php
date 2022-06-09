<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdministratorRequest;
use App\Http\Requests\CreateApiKeyRequest;
use App\Repositories\Eloquent\ApiKeyRepository;

class ApiKeyController extends Controller
{
    /**
     * @param  AdministratorRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(AdministratorRequest $request): \Illuminate\Http\Response
    {
        return response()->view('pages.api-keys');
    }

    /**
     * @param  AdministratorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(AdministratorRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(app(ApiKeyRepository::class)->get());
    }

    /**
     * @param  CreateApiKeyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateApiKeyRequest $request): \Illuminate\Http\JsonResponse
    {
        app(ApiKeyRepository::class)->store(['name' => $request->input('name')]);

        return response()->json();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AdministratorRequest $request
     * @param  int                  $apiKeyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(AdministratorRequest $request, int $apiKeyId): \Illuminate\Http\JsonResponse
    {
        app(ApiKeyRepository::class)->delete($apiKeyId);

        return response()->json();
    }
}

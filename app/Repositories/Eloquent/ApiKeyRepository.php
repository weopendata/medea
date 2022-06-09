<?php

namespace App\Repositories\Eloquent;

use App\Models\Eloquent\ApiKey;
use Ramsey\Uuid\Uuid;

class ApiKeyRepository
{
    /**
     * @var ApiKey 
     */
    private $model;
    
    /**
     * @param  ApiKey $model
     */
    public function __construct(ApiKey $model)
    {
        $this->model = $model;
    }

    /**
     * @param  array $model
     * @return int
     */
    public function store(array $model): int
    {
        $apiKey = $this
            ->model
            ->create([
                'name' => $model['name'],
                'api_key' => Uuid::uuid4()
            ]);

        return $apiKey->id;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this
            ->model
            ->get()
            ->toArray();
    }

    /**
     * @param  int $apiKeyId
     * @return void
     */
    public function delete(int $apiKeyId)
    {
        return $this
            ->model
            ->where('id', $apiKeyId)
            ->delete();
    }
}
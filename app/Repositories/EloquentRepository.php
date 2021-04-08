<?php

namespace App\Repositories;

/**
 * Class EloquentRepository
 * @package App\Repositories
 */
class EloquentRepository
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getById($modelId)
    {
        return $this->model->find($modelId);
    }

    public function store(array $config)
    {
        $model = $this->model->create($config);

        return $model->save();
    }

    public function delete($modelId)
    {
        $model = $this->model->find($modelId);

        if (!empty($model)) {
            return $model->delete();
        }

        return false;
    }

    public function update($modelId, $config)
    {
        $model = $this->model->find($modelId);

        if (!empty($model)) {
            return $model->update($config);
        }

        return false;
    }
}

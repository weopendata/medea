<?php


namespace App\Repositories\Eloquent;


use App\Models\Eloquent\FileUpload;

class FileUploadRepository
{
    /**
     * @var FileUpload
     */
    private $model;

    public function __construct(FileUpload $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $fileUpload
     * @return mixed
     */
    public function store(array $fileUpload)
    {
        $fileUpload = $this
            ->model
            ->create($fileUpload);

        return $fileUpload->id;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this
            ->model
            ->orderByDesc('id')
            ->get()
            ->map(function ($result) {
                $importJobs = $result
                    ->importJobs
                    ->map(function ($job) {
                        return [
                            'id' => $job->id,
                            'status' => $job->status
                        ];
                    })
                    ->toArray();

                $result = $result->toArray();

                $result = array_only($result, [
                    'id',
                    'name',
                    'user_name',
                    'last_imported',
                    'created_at'
                ]);

                $result['import_jobs'] = $importJobs;

                return $result;
            })
            ->toArray();
    }
}

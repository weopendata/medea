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
                $fileStatus = 'not imported';

                $importJobs = $result
                    ->importJobs
                    ->map(function ($job) use (&$fileStatus) {
                        if (in_array($job->status, ['running', 'queued'])) {
                            $fileStatus = $job->status;
                        }

                        return [
                            'id' => $job->id,
                            'status' => $job->status,
                            'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                        ];
                    })
                    ->toArray();

                if ($fileStatus == 'not imported' && !empty($importJobs)) {
                    $fileStatus = 'finished';
                }

                $result = $result->toArray();

                $result = array_only($result, [
                    'id',
                    'name',
                    'user_name',
                    'last_imported',
                    'created_at',
                    'type',
                ]);

                $result['import_jobs'] = $importJobs;

                $result['status'] = $fileStatus;

                return $result;
            })
            ->toArray();
    }

    /**
     * @param integer $uploadId
     */
    public function delete($uploadId)
    {
        $fileUpload = $this->model->find($uploadId);

        if (empty($fileUpload)) {
            return;
        }

        $path = $fileUpload->path;

        $fileUpload->delete();

        $filePath = storage_path('app/') . $path;

        unlink($filePath);
    }
}

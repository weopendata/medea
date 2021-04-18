<?php


namespace App\Repositories\Eloquent;


use App\Models\Eloquent\ImportLog;

class ImportLogRepository
{
    private $model;

    public function __construct(ImportLog $model)
    {
        $this->model = $model;
    }

    public function store($log)
    {
        return $this->model->create($log);
    }

    /**
     * @param int $jobId
     * @return array
     */
    public function getLogsForJob($jobId)
    {
        $logs = ImportLog::where('import_jobs_id', $jobId)
            ->get()
            ->map(function ($log) {
               $context = $log->context ?? [];

                return [
                    'id' => $log->id,
                    'level' => $log->level,
                    'message' => $log->message,
                    'line_number' => $log->line_number,
                    'action' => $log->action,
                    'status' => $log->status,
                    'object_identifier' => array_get($context, 'identifier')
                ];
            })
            ->toArray();

            return $logs;
    }
}

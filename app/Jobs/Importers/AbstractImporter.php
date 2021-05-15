<?php


namespace App\Jobs\Importers;


use App\Repositories\Eloquent\ImportLogRepository;

abstract class AbstractImporter
{
    private $importJobId;

    /**
     * AbstractImporter constructor.
     * @param integer $importJobId
     */
    public function __construct($importJobId)
    {
        $this->importJobId = $importJobId;
    }

    abstract public function processData(array $data, int $index);


    /**
     * @param $index
     * @param $message
     * @param $action
     * @param $context
     * @param $success
     */
    protected function addLog($index, $message, $action, $context, $success)
    {
        app(ImportLogRepository::class)->store([
            'line_number' => $index,
            'action' => $action,
            'import_jobs_id' => $this->importJobId,
            'level' => 'INFO',
            'message' => $message,
            'context' => $context,
            'status' => $success ? 'success' : 'failed',
        ]);
    }
}

<?php

namespace App\Jobs;

use App\Import\Csv;
use App\Jobs\Importers\ImportContexts;
use App\Jobs\Importers\ImportExcavations;
use App\Jobs\Importers\ImportFinds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var integer
     */
    private $fileUploadId;

    /**
     * @var
     */
    protected $importJob;

    /**
     * Create a new job instance.
     *
     * @param $importJob
     */
    public function __construct($importJob)
    {
        $this->importJob = $importJob;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $fileUpload = $this->importJob->fileUpload;

        $filePath =  storage_path('app/' . $fileUpload->path);

        if (! file_exists($filePath)) {
            $this->importJob->status = 'failed';
            $this->importJob->context = 'The CSV file could not be found.';
            $this->importJob->save();

            return;
        }

        // Update the import job status
        $this->updateStatus('running');

        // Fetch the processor based on the file upload content
        $processor = $this->getProcessor($fileUpload->type);

        // Read the CSV file and process the data
        $csvReader = new Csv($filePath);

        $data = $csvReader->getNext();

        while (!empty($data)) {
            $processor->processData($data, $csvReader->getIndex());

            $data = $csvReader->getNext();
        }

        $this->updateStatus('finished');
    }

    /**
     * @param string $status
     */
    private function updateStatus($status)
    {
        $this->importJob->status = $status;
        $this->importJob->save();
    }

    private function getProcessor(string $fileUploadType)
    {
        if ($fileUploadType == 'excavation') {
            return new ImportExcavations($this->importJob->id);
        } else if ($fileUploadType == 'context') {
            return new ImportContexts($this->importJob->id);
        } else if ($fileUploadType == 'find') {
            return new ImportFinds($this->importJob->id);
        }

        throw new \Exception("No processor found for $fileUploadType");
    }

    /**
     * @param \Throwable $ex
     */
    public function failed(\Throwable $ex)
    {
        \Log::error($ex->getMessage());
        \Log::error($ex->getTraceAsString());

        $this->importJob->status = 'failed';
        $this->importJob->save();
    }
}

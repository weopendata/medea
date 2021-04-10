<?php

namespace App\Jobs;

use App\Import\Csv;
use App\Models\Eloquent\ImportJob;
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
     * @var
     */
    protected $importJobId;

    /**
     * Create a new job instance.
     *
     * @param $importJobId
     */
    public function __construct($importJobId)
    {
        $this->importJobId = $importJobId;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->importJob = ImportJob::findOrFail($this->importJobId);

        $fileUpload = $this->importJob->fileUpload;

        $filePath =  storage_path('app/' . $fileUpload->path);

        if (! file_exists($filePath)) {
            $this->importJob->status = 'failed';
            $this->importJob->context = 'The CSV file could not be found.';
            $this->importJob->save();

            return;
        }

        // Update the import job status
        $this->importJob->status = 'running';
        $this->importJob->save();

        // Fetch the processor based on the file upload content
        $processor = $this->getProcessor($fileUpload->type);

        // Read the CSV file and process the data
        $csvReader = new Csv($filePath);

        $data = $csvReader->getNext();

        while (!empty($data)) {
            $processor->processData($data, $csvReader->getIndex());

            $data = $csvReader->getNext();

            return $data;
        }
    }

    private function getProcessor(string $fileUploadType)
    {
        if ($fileUploadType == 'excavation') {
            return new ImportExcavations($this->importJob->id);
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

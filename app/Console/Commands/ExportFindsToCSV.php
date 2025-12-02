<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\FindRepository;
use App\Models\FindEvent;
use App\Services\NodeService;
use League\Csv\Writer;

class ExportFindsToCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medea:export-finds-csv {--output= : Output file path (default: storage/app/finds_export.csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all find basic data to CSV';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FindRepository $finds)
    {
        parent::__construct();
        $this->finds = $finds;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $outputPath = $this->option('output') ?: storage_path('app/finds_export.csv');

        $this->info('Starting find export...');

        // Get all find nodes
        $findNodes = $this->finds->getAll();
        $totalFinds = count($findNodes);

        if ($totalFinds === 0) {
            $this->warn('No finds found to export.');
            return;
        }

        $this->info("Found {$totalFinds} finds to export.");

        // Create CSV writer
        $csv = Writer::createFromPath($outputPath, 'w+');

        // Define CSV headers
        $headers = [
            'MEDEA ID',
            'Internal ID',
            'Find Date',
            'Location (Locality)',
            'Latitude',
            'Longitude',
            'Coordinate Accuracy',
            'Find Spot Type',
            'Find Spot Title',
            'Finder Name',
            'Finder Email',
            'Finder Detectorist Number',
            'Object Category',
            'Object Material',
            'Object Period',
            'Object Description',
            'Production Technique',
            'Modification Technique',
            'Inscription Note',
            'Dimensions',
            'Collection Title',
            'Object Number',
            'Validation Status',
            'Validated By',
            'Validated At',
            'Created At',
            'Updated At',
            'Classification Count'
        ];

        $csv->insertOne($headers);

        $bar = $this->output->createProgressBar($totalFinds);
        $exportedCount = 0;
        $errorCount = 0;

        foreach ($findNodes as $findNode) {
            try {
                $find = new FindEvent();
                $find->setNode($findNode);

                // Get the full data
                $findData = $find->getValues();

                // Extract and format data
                $row = $this->extractFindData($findData);

                $csv->insertOne($row);
                $exportedCount++;
            } catch (\Exception $ex) {
                $errorCount++;
                $this->error("\nError exporting find ID {$findNode->getId()}: " . $ex->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        $this->info('');
        $this->info("Export completed successfully!");
        $this->info("Exported {$exportedCount} finds to: {$outputPath}");

        if ($errorCount > 0) {
            $this->warn("Encountered {$errorCount} errors during export.");
        }
    }

    /**
     * Extract and format find data for CSV export
     *
     * @param array $findData
     * @return array
     */
    private function extractFindData($findData)
    {
        // Helper function to safely get nested array values
        $get = function($array, $key, $default = '') {
            return data_get($array, $key, $default);
        };

        // Get MEDEA UUID
        $medeaId = $get($findData, 'identifier.MEDEA_UUID', $get($findData, 'identifier', ''));

        // Get finder information
        $finderName = '';
        if (!empty($findData['person'])) {
            $showName = $get($findData, 'person.showNameOnPublicFinds', true);
            if ($showName) {
                $firstName = $get($findData, 'person.firstName', '');
                $lastName = $get($findData, 'person.lastName', '');
                $finderName = trim($firstName . ' ' . $lastName);
            }
        }

        // Get dimensions as a formatted string
        $dimensions = '';
        if (!empty($findData['object']['dimensions'])) {
            $dimStrings = [];
            foreach ($findData['object']['dimensions'] as $dim) {
                $type = $get($dim, 'dimensionType', '');
                $value = $get($dim, 'measurementValue', '');
                $unit = $get($dim, 'dimensionUnit', '');
                if ($type && $value) {
                    $dimStrings[] = "{$type}: {$value}{$unit}";
                }
            }
            $dimensions = implode('; ', $dimStrings);
        }

        // Count classifications
        $classificationCount = 0;
        if (!empty($findData['object']['productionEvent']['productionClassification'])) {
            $classificationCount = count($findData['object']['productionEvent']['productionClassification']);
        }

        return [
            $medeaId, // MEDEA ID
            $get($findData, 'identifier', ''), // Internal ID
            $get($findData, 'findDate', ''), // Find Date
            $get($findData, 'findSpot.location.address.locationAddressLocality', ''), // Location
            $get($findData, 'findSpot.location.lat', ''), // Latitude
            $get($findData, 'findSpot.location.lng', ''), // Longitude
            $get($findData, 'findSpot.location.accuracy', ''), // Coordinate Accuracy
            $get($findData, 'findSpot.findSpotType', ''), // Find Spot Type
            $get($findData, 'findSpot.findSpotTitle', ''), // Find Spot Title
            $finderName, // Finder Name
            $get($findData, 'person.email', ''), // Finder Email
            $get($findData, 'person.detectoristNumber', ''), // Finder Detectorist Number
            $get($findData, 'object.objectCategory', ''), // Object Category
            $get($findData, 'object.objectMaterial', ''), // Object Material
            $get($findData, 'object.period', ''), // Object Period
            $get($findData, 'object.objectDescription', ''), // Object Description
            $get($findData, 'object.productionEvent.productionTechnique.productionTechniqueType', ''), // Production Technique
            $get($findData, 'object.treatmentEvent.modificationTechnique.modificationTechniqueType', ''), // Modification Technique
            $get($findData, 'object.objectInscription.objectInscriptionNote', ''), // Inscription Note
            $dimensions, // Dimensions
            $get($findData, 'object.collection.title', ''), // Collection Title
            $get($findData, 'object.objectNr', ''), // Object Number
            $get($findData, 'object.objectValidationStatus', ''), // Validation Status
            $get($findData, 'object.validated_by', ''), // Validated By
            $get($findData, 'object.validated_at', ''), // Validated At
            $get($findData, 'created_at', ''), // Created At
            $get($findData, 'updated_at', ''), // Updated At
            $classificationCount // Classification Count
        ];
    }
}

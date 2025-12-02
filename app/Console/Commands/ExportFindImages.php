<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\FindRepository;
use App\Models\FindEvent;
use App\Services\NodeService;
use League\Csv\Writer;
use ZipArchive;

class ExportFindImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medea:export-images {--output= : Output directory (default: storage/app/image_export)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all find images to a zip file with mapping CSV';

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
        $outputDir = $this->option('output') ?: storage_path('app/image_export');

        // Create output directory if it doesn't exist
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $tempDir = $outputDir . '/temp_images';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $this->info('Starting image export...');

        // Get all find nodes (pass null for limit to get ALL finds)
        $findNodes = $this->finds->getAll(null, 0);
        $totalFinds = count($findNodes);

        if ($totalFinds === 0) {
            $this->warn('No finds found to export.');
            return;
        }

        $this->info("Found {$totalFinds} finds to process.");

        // Create CSV writer for mapping
        $mappingCsvPath = $outputDir . '/image_mapping.csv';
        $csv = Writer::createFromPath($mappingCsvPath, 'w+');

        // Define CSV headers
        $headers = [
            'MEDEA ID',
            'Vondst ID',
            'Internal Find ID',
            'Image Number',
            'Filename',
            'Original Path',
            'Width',
            'Height'
        ];

        $csv->insertOne($headers);

        $bar = $this->output->createProgressBar($totalFinds);
        $totalImages = 0;
        $copiedImages = 0;
        $errorCount = 0;

        foreach ($findNodes as $findNode) {
            try {
                $find = new FindEvent();
                $find->setNode($findNode);

                // Get the full data
                $findData = $find->getValues();

                // Get MEDEA UUID
                $medeaId = data_get($findData, 'identifier.MEDEA_UUID', data_get($findData, 'identifier', ''));
                $vondstId = data_get($findData, 'identifier', ''); // Vondst ID (displayed as ID-{identifier} on detail page)
                $internalId = data_get($findData, 'identifier', '');

                // Get photographs
                $photographs = data_get($findData, 'object.photograph', []);

                if (!empty($photographs) && is_array($photographs)) {
                    foreach ($photographs as $index => $photo) {
                        $totalImages++;
                        $imageNumber = $index + 1;

                        // Get the source path
                        $srcPath = data_get($photo, 'src', '');

                        if (empty($srcPath)) {
                            continue;
                        }

                        // Handle both relative paths and full URLs
                        if (filter_var($srcPath, FILTER_VALIDATE_URL)) {
                            // It's a full URL - extract the path portion
                            $parsedUrl = parse_url($srcPath);
                            $relativePath = $parsedUrl['path'] ?? '';
                        } else {
                            // It's already a relative path
                            $relativePath = $srcPath;
                        }

                        // Construct full path to the image
                        $fullSrcPath = public_path($relativePath);

                        // Check if file exists
                        if (!file_exists($fullSrcPath)) {
                            $this->warn("\nImage not found: {$fullSrcPath}");
                            $errorCount++;
                            continue;
                        }

                        // Get file extension
                        $extension = pathinfo($fullSrcPath, PATHINFO_EXTENSION);

                        // Create new filename: medeaID_number.ext
                        $newFilename = $medeaId . '_' . $imageNumber . '.' . $extension;
                        $destPath = $tempDir . '/' . $newFilename;

                        // Copy file
                        if (copy($fullSrcPath, $destPath)) {
                            $copiedImages++;

                            // Add to CSV mapping
                            $csv->insertOne([
                                $medeaId,
                                $vondstId,
                                $internalId,
                                $imageNumber,
                                $newFilename,
                                $srcPath,
                                data_get($photo, 'width', ''),
                                data_get($photo, 'height', '')
                            ]);
                        } else {
                            $this->warn("\nFailed to copy: {$fullSrcPath}");
                            $errorCount++;
                        }
                    }
                }
            } catch (\Exception $ex) {
                $errorCount++;
                $this->error("\nError processing find ID {$findNode->getId()}: " . $ex->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info('');

        // Create ZIP file
        $this->info('Creating ZIP archive...');
        $zipPath = $outputDir . '/medea_images.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add all files from temp directory
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = basename($filePath);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            $this->info("ZIP archive created: {$zipPath}");

            // Clean up temp directory
            $this->info('Cleaning up temporary files...');
            $this->deleteDirectory($tempDir);
        } else {
            $this->error('Failed to create ZIP archive.');
            return 1;
        }

        $this->info('');
        $this->info("Export completed successfully!");
        $this->info("Total images found: {$totalImages}");
        $this->info("Images copied: {$copiedImages}");
        $this->info("ZIP file: {$zipPath}");
        $this->info("Mapping CSV: {$mappingCsvPath}");

        if ($errorCount > 0) {
            $this->warn("Encountered {$errorCount} errors during export.");
        }
    }

    /**
     * Recursively delete a directory
     *
     * @param string $dir
     * @return bool
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}

<?php

namespace App\Console\Commands;

use League\Csv\Reader;
use Illuminate\Console\Command;

class ImportFinds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medea:import-finds {file : The full path to the CSV file containing structured information about finds.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import finds through a CSV file.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error('The file that was passed was not found, make sure that the path is correct.');

            return;
        }

        $csv = Reader::createFromPath($file);
        $csv->setDelimiter(';');

        foreach ($csv->fetchAssoc() as $row) {
            $find = [];

            // Build the find with the properties of the row
            foreach ($row as $property => $value) {
                if (! empty($value)) {
                    $method = 'set' . studly_case($property);

                    $find = $this->$method($find, $value);
                }
            }

            // Store the find and return the result (success or not)
            dd($find);
        }
    }

    /**
     * Set the objectNr on the object
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setObjectNr($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['objectNr'] = $value;

        return $find;
    }

    private function setWeight($find, $value)
    {
        $find = $this->initObject($find);

        if (empty($find['object']['dimensions'])) {
            $find['object']['dimensions'] = [];
        }

        $find['object']['dimensions'][] = [
            'dimensionType' => 'gewicht',
            'dimensionUnit' => 'g',
            'measurementValue' => $value
        ];

        return $find;
    }

    private function setDiameter($find, $value)
    {
        $find = $this->initObject($find);

        if (empty($find['object']['dimensions'])) {
            $find['object']['dimensions'] = [];
        }

        $find['object']['dimensions'][] = [
            'dimensionType' => 'diameter',
            'dimensionUnit' => 'mm',
            'measurementValue' => $value
        ];

        return $find;
    }

    private function setCity($find, $value)
    {
        $find = $this->initFindSpot($find);

        if (empty($find['findSpot']['location']['address'])) {
            if (empty($find['findSpot']['location'])) {
                $find['findSpot']['location'] = [];
            }

            $find['findSpot']['location']['address'] = ['locationAddressLocality' => $value];
        }

        return $find;
    }

    private function setFindDate($find, $value)
    {
        $find['findDate'] = $value;

        return $find;
    }

    private function setMaterial($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['objectMaterial'] = $value;

        return $find;
    }

    private function setPeriod($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['period'] = $value;

        return $find;
    }

    private function setCategory($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['objectCategory'] = $value;

        return $find;
    }

    /**
     * Set the inscription on the object
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setInscription($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['objectInscription'] = ['objectInscription' => $value];

        return $find;
    }

     /**
     * Set the end date on classification
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setEndPeriodClassification($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['endDate'] = $value;

        return $find;
    }

    /**
     * Set the start date on classification
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setStartPeriodClassification($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['startDate'] = $value;

        return $find;
    }

    /**
     * Set the production classification ruler
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setRuler($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['productionClassificationRulerNation'] = $value;

        return $find;
    }

    /**
     * Set the production classification type
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setClassificationType($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['productionClassificationType'] = 'Typologie';
        $find['object']['productionEvent']['productionClassification'][0]['productionClassificationValue'] = $value;

        return $find;
    }

    /**
     * Make sure that the productionClassification exists
     *
     * @param  array $find
     * @return $find
     */
    private function initClassification($find)
    {
        if (empty($find['object']['productionEvent']['productionClassification'])) {
            $find = $this->initObject($find);
            $find['object']['productionEvent'] = [];
            $find['object']['productionEvent']['productionClassification'] =  [];
        }

        return $find;
    }

    /**
     * Make sure a find spot entry is present in the find array
     *
     * @param  array $find
     * @return array
     */
    private function initFindSpot($find)
    {
        if (empty($find['findSpot'])) {
            $find['findSpot'] = [];
        }

        return $find;
    }

    /**
     * Make sure an object entry is present in the find array
     *
     * @param  array $find
     * @return array
     */
    private function initObject($find)
    {
        if (empty($find['object'])) {
            $find['object'] = [];
        }

        return $find;
    }
}

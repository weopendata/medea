<?php

namespace App\Console\Commands;

use App\Models\FindEvent;
use App\Repositories\FindRepository;
use App\Repositories\ClassificationRepository;
use App\Traits\ValidateFields;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportFinds extends Command
{
    use ValidateFields;

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

        // Keep track of the (human) row index in the CSV
        $index = 1;

        foreach ($csv->fetchAssoc() as $row) {
            $find = [];

            try {
                // Build the find with the properties of the row
                foreach ($row as $property => $value) {
                    // Some properties are "update only", such as publication and publicationPage
                    if (! empty($value) && ! in_array($property, ['publication'])) {
                        $method = 'set' . studly_case($property);

                        // Validate the value for the property
                        $validatedValue = $this->validate($property, $value);

                        if (! $validatedValue) {
                            throw new \Exception("The value $value for property $property is not correct. Make sure it's a correct value (check the authority lists)");
                        }

                        $value = $validatedValue;

                        $find = $this->$method($find, $value);
                    }
                }

                $find['object']['objectValidationStatus'] = 'Klaar voor validatie';

                if (empty($find['findDate'])) {
                    $find['findDate'] = 'onbekend';
                }

                $adminId = $this->getAdminId();

                if (empty($adminId)) {
                    $this->error('There was no admin user found, please make sure the Medea Admin user has been added so it can be used to be attached to this find.');
                }

                $find['person']['id'] = $adminId;

                // Store the find and return the result (success or not)
                $findId = app(FindRepository::class)->store($find);

                $this->info("A find was created from row $index, the ID of the FindEvent is: $findId");

                // Check if we need to update the find with publication for example
                $this->update($findId, $row);
            } catch (\Exception $ex) {
                $this->error("Something went wrong when trying to make a find from the CSV file, row $index: " . $ex->getMessage());
                \Log::error($ex->getTraceAsString());
            }

            $index++;
        }
    }

    /**
     * Update the find, currently only adding a publication is supported
     *
     * @param  int   $findId
     * @param  array $row
     * @return void
     */
    private function update($findId, $row)
    {
        $find = app(FindRepository::class)->expandValues($findId);

        $publicationId = array_get($row, 'publication');

        if (empty($publicationId)) {
            return;
        }

        // If the publication is added, a classification must exist!
        $classification = array_get($find, 'object.productionEvent.productionClassification.0');

        if (empty($classification)) {
            $this->error('No classification was found, we could not add the publication properties');
        }

        $classificationNode = app(ClassificationRepository::class)->getById($classification['identifier']);

        if (! empty($row['publication'])) {
            app(ClassificationRepository::class)->linkPublications($classificationNode, [(int) $row['publication']]);
        }
    }

    /**
     * Get the admin user ID of the platform
     *
     * return integer
     */
    private function getAdminId()
    {
         $neo4j_config = \Config::get('database.connections.neo4j');

        // Create an admin
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        $query = 'MATCH (person:person) WHERE person.firstName="Medea" and person.lastName="Admin" return person';

        $cypherQuery = new Query($client, $query, []);

        $results = $cypherQuery->getResultSet();

        // We assume that the platform was seeded already, which includes adding an admin user
        return $results[0]['person']->getId();
    }

    /**
     * Set publication page
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setPublicationPage($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['productionClassificationSource'] = (int) $value;

        return $find;
    }

    /**
     * Set the findSpotTypeDescription
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setFindSpotTypeDescription($find, $value)
    {
        if (empty($find['findSpot'])) {
            $find['findSpot'] = [];
        }

        $find['findSpot']['findSpotTypeDescription'] = $value;

        return $find;
    }

    /**
     * Set the findSpotType
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setFindSpotType($find, $value)
    {
        if (empty($find['findSpot'])) {
            $find['findSpot'] = [];
        }

        $find['findSpot']['findSpotType'] = $value;

        return $find;
    }

     /**
     * Set the findSpotTitle
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setfindSpotTitle($find, $value)
    {
        if (empty($find['findSpot'])) {
            $find['findSpot'] = [];
        }

        $find['findSpot']['findSpotTitle'] = $value;

        return $find;
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
            'measurementValue' => (double) $value
        ];

        return $find;
    }

    private function setWidth($find, $value)
    {
        $find = $this->initObject($find);

        if (empty($find['object']['dimensions'])) {
            $find['object']['dimensions'] = [];
        }

        $find['object']['dimensions'][] = [
            'dimensionType' => 'breedte',
            'dimensionUnit' => 'mm',
            'measurementValue' => (double) $value
        ];

        return $find;
    }

    private function setLength($find, $value)
    {
        $find = $this->initObject($find);

        if (empty($find['object']['dimensions'])) {
            $find['object']['dimensions'] = [];
        }

        $find['object']['dimensions'][] = [
            'dimensionType' => 'lengte',
            'dimensionUnit' => 'mm',
            'measurementValue' => (double) $value
        ];

        return $find;
    }

    private function setHeight($find, $value)
    {
        $find = $this->initObject($find);

        if (empty($find['object']['dimensions'])) {
            $find['object']['dimensions'] = [];
        }

        $find['object']['dimensions'][] = [
            'dimensionType' => 'diepte',
            'dimensionUnit' => 'mm',
            'measurementValue' => (double) $value
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
            'measurementValue' => (double) $value
        ];

        return $find;
    }

    private function setLatitude($find, $value)
    {
        $find = $this->initFindSpot($find);

        if (empty($find['findSpot']['location'])) {
            $find['findSpot']['location'] = [];
        }

        $find['findSpot']['location']['lat'] = (double) $value;

        return $find;
    }

    private function setLongitude($find, $value)
    {
        $find = $this->initFindSpot($find);

        if (empty($find['findSpot']['location'])) {
            $find['findSpot']['location'] = [];
        }

        $find['findSpot']['location']['lng'] = (double) $value;

        return $find;
    }

    private function setAccuracy($find, $value)
    {
        $find = $this->initFindSpot($find);

        if (empty($find['findSpot']['location'])) {
            $find['findSpot']['location'] = [];
        }

        $find['findSpot']['location']['accuracy'] = (int) $value;

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

    private function setObjectDescription($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['objectDescription'] = $value;

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
        $find['object']['objectInscription'] = ['objectInscriptionNote' => $value];

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
     * Set the type of classification
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setClassificationType($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['productionClassificationType'] = $value;

        return $find;
    }

    /**
     * Set the value of classification
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setClassificationValue($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['productionClassificationValue'] = $value;

        return $find;
    }

    /**
     * Set the productionevent technique
     *
     * @param  array $find
     * @param  value $value
     * @return array
     */
    private function setTechnique($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionTechnique'] = ['productionTechniqueType' => $value];

        return $find;
    }

    /**
     * Set the surface treatment
     *
     * @param  array $find
     * @param  value $value
     * @return array
     */
    private function setSurfaceTreatment($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['treatmentEvent'] = ['modificationTechnique' => ['modificationTechniqueType' => $value]];

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
     * Set the production classification ruler nation
     *
     * @param  array  $find
     * @param  string $value
     * @return array
     */
    private function setNation($find, $value)
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
    private function setClassificationTypeValue($find, $value)
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

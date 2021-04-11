<?php


namespace App\Jobs\Importers;


use App\Models\FindEvent;
use App\Repositories\ContextRepository;
use App\Repositories\FindRepository;
use App\Repositories\ObjectRepository;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;

class ImportFinds extends AbstractImporter
{
    /**
     * @param array $data
     * @param int $index
     */
    public function processData(array $data, int $index)
    {
        try {
            $action = '';

            $find = $this->buildFindModel($data);

            $existingFind = app(FindRepository::class)->getByInternalId($find['internalId']);

            if (! empty($existingFind)) {
                $findId = $existingFind->getId();
                $existingFind = app(FindRepository::class)->expandValues($findId);

                $find = $this->buildFindModel($data, $existingFind);
            }

            $action = !empty($existingFind) ? 'update' : 'create';

            $context = @$find['object']['context'];

            unset($find['object']['context']);

            if ($action == 'update') {
                app(FindRepository::class)->update($findId, $find);

                $this->addLog($index, 'Updated a find ', $action, ['identifier' => $findId, 'data' => $data], true);
            } else {
                $findId = app(FindRepository::class)->store($find);

                $this->addLog($index, 'Added a find ', $action, ['identifier' => $findId, 'data' => $data], true);
            }

            if (!empty($context)) {
                $find = app(FindRepository::class)->expandValues($findId);

                app(ObjectRepository::class)->linkWithContext($find['object']['identifier'], $context['id']);
            }
        } catch (\Exception $ex) {
            \Log::error($ex->getMessage());
            \Log::error($ex->getTraceAsString());

            $this->addLog($index, 'Something went wrong: ' . $ex->getMessage(), $action, ['data' => $data], false);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function buildFindModel(array $data, $find = [])
    {
        $data = $this->transformHeaders($data);

        // If the find is not empty, remove a couple of things we know will be overwritten
        unset($find['object']['dimensions']);
        unset($find['object']['objectInscription']);

        foreach ($data as $property => $value) {
            // Some properties are "update only", such as publication and publicationPage
            if (!empty($value)) {
                $method = 'set' . studly_case($property);

                if (! method_exists($this, $method)) {
                    continue;
                }

                $find = $this->$method($find, $value);
            }
        }

        // Add the internal ID
        $find['internalId'] = $data['findUUID'];

        if (empty($find['object']['objectValidationStatus'])) {
            $find['object']['objectValidationStatus'] = 'Klaar voor validatie';
        }

        if (empty($find['findDate'])) {
            $find['findDate'] = 'onbekend';
        }

        $adminId = $this->getAdminId();

        $find['person']['id'] = $adminId;

        return $find;
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
     * @param array $find
     * @param string $value
     * @return array
     */
    private function setPublicationPage($find, $value)
    {
        $find = $this->initClassification($find);
        $find['object']['productionEvent']['productionClassification'][0]['productionClassificationSource'] = (int)$value;

        return $find;
    }

    /**
     * Set the findSpotTypeDescription
     *
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
     * @return array
     */
    private function setObjectNr($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['objectNr'] = $value;

        return $find;
    }

    /**
     * Set the objectMaterial on the object
     *
     * @param array $find
     * @param string $value
     * @return array
     */
    private function setObjectMaterial($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['objectMaterial'] = $value;

        return $find;
    }

    /**
     * Set the photograph
     *
     * @param $find
     * @param $value
     * @return array
     */
    private function setPhotograph($find, $value)
    {
        $find = $this->initObject($find);
        $find['object']['photograph'] = [
            [
                'src' => $value
            ]
        ];

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
            'measurementValue' => (double)$value
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
            'measurementValue' => (double)$value
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
            'measurementValue' => (double)$value
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
            'measurementValue' => (double)$value
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
            'measurementValue' => (double)$value
        ];

        return $find;
    }

    private function setLatitude($find, $value)
    {
        $find = $this->initFindSpot($find);

        if (empty($find['findSpot']['location'])) {
            $find['findSpot']['location'] = [];
        }

        $find['findSpot']['location']['lat'] = (double)$value;

        return $find;
    }

    private function setLongitude($find, $value)
    {
        $find = $this->initFindSpot($find);

        if (empty($find['findSpot']['location'])) {
            $find['findSpot']['location'] = [];
        }

        $find['findSpot']['location']['lng'] = (double)$value;

        return $find;
    }

    private function setAccuracy($find, $value)
    {
        $find = $this->initFindSpot($find);

        if (empty($find['findSpot']['location'])) {
            $find['findSpot']['location'] = [];
        }

        $find['findSpot']['location']['accuracy'] = (int)$value;

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

    private function setContext($find, $value)
    {
        $context = app(ContextRepository::class)->getByInternalId($value);

        if (empty($context)) {
            return $find;
        }

        $find = $this->initObject($find);
        $find['object']['context'] = ['id' => $context->getId()];

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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param value $value
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
     * @param array $find
     * @param value $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @param string $value
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
     * @param array $find
     * @return $find
     */
    private function initClassification($find)
    {
        if (empty($find['object']['productionEvent']['productionClassification'])) {
            $find = $this->initObject($find);
            $find['object']['productionEvent'] = [];
            $find['object']['productionEvent']['productionClassification'] = [];
        }

        return $find;
    }

    /**
     * Make sure a find spot entry is present in the find array
     *
     * @param array $find
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
     * @param array $find
     * @return array
     */
    private function initObject($find)
    {
        if (empty($find['object'])) {
            $find['object'] = [];
        }

        return $find;
    }

    private function transformHeaders(array $data)
    {
        $result = [];

        $mapping = [
            'id' => 'findUUID',
            'inventarisnummer' => 'objectNr',
            'context' => 'context',
            'foto' => 'photograph',
            'opmerkingen foto' => 'photographRemarks',
            'trefwoord' => 'objectCategory',
            'fysieke beschrijving' => 'objectDescription',
            'materiaal' => 'objectMaterial',
            'techniek' => 'technique',
            'oppervlaktebehandeling' => 'surfaceTreatment',
            'opschrift' => 'inscription',
            'lengte (mm)' => 'length',
            'breedte (mm)' => 'width',
            'dikte (mm)' => 'height',
            'diameter (mm)' => 'diameter',
            'gewicht (g)' => 'weight'
        ];

        foreach ($mapping as $key => $newKey) {
            $result[$newKey] = @$data[$key];
        }

        return $result;
    }
}

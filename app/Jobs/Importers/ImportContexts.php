<?php


namespace App\Jobs\Importers;

use App\Models\Context;
use App\Models\SearchArea;
use App\Repositories\ContextRepository;
use App\Repositories\SearchAreaRepository;

class ImportContexts extends AbstractImporter
{
    /**
     * @param array $data
     * @param int $index
     * @return void
     */
    public function processData(array $data, int $index)
    {
        $isValid = $this->validate($data, $index);

        if (!$isValid) {
            return;
        }

        // Do some small transformations on the data to make it robust
        $data = $this->transformData($data);

        // The contextId is unique, check if the context already exists or not
        $existingContext = app(ContextRepository::class)->getByInternalId($data['id']);

        $action = !empty($existingContext) ? 'update' : 'create';

        try {
            $contextModel = $this->createContextModel($data);

            if ($action == 'update') {
                $contextModelId = $existingContext->getId();

                app(ContextRepository::class)->update($contextModelId, $contextModel);

                $this->addLog($index, 'Updated a context ', $action, ['identifier' => $contextModelId, 'data' => $data], true);
            } else {
                $contextModelId = app(ContextRepository::class)->store($contextModel);

                $this->addLog($index, 'Added a context ', $action, ['identifier' => $contextModelId, 'data' => $data], true);
            }
        } catch (\Exception $ex) {
            $this->addLog($index, 'Something went wrong: ' . $ex->getMessage(), $action, ['data' => $data, 'trace' => $ex->getTraceAsString()], false);

            \Log::error($ex->getTraceAsString());
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function createContextModel(array $data)
    {
        $contextId = $data['id'];

        $model = [];
        $model['internalId'] = $contextId;
        $model['contextId'] = ['contextIdValue' => $contextId];
        $model['contextLegacyId'] = ['contextLegacyIdValue' => array_get($data, 'legacyId')];
        $model['contextType'] = array_get($data, 'contextType');
        $model['contextInterpretation'] = array_get($data, 'contextInterpretation');

        // Only for a C0 context we make a SearchArea
        if ($data['local_context_id'] == 'C0') {
            // Find the search area or create one
            $searchAreaInternalId = SearchArea::createInternalId($contextId);
            $existingSearchArea = app(SearchAreaRepository::class)->getByInternalId($searchAreaInternalId);

            if (empty($existingSearchArea)) {
                $searchAreaId = app(SearchAreaRepository::class)->store(['internalId' => $searchAreaInternalId]);

                if (!empty($searchAreaId)) {
                    $model['searchArea'] = ['id' => $searchAreaId];
                }
            } else {
                $model['searchArea'] = ['id' => $existingSearchArea->getId()];
            }

            if (empty($model['searchArea'])) {
                throw new \Exception('Could not find or create the searchArea');
            }
        }

        if (!empty($data['relatedContext'])) {
            $relatedContextId = Context::createInternalId($data['relatedContext'], $data['excavationId']);

            $existingContext = app(ContextRepository::class)->getByInternalId($relatedContextId);

            if (!empty($existingContext)) {
                $model['context'] = ['id' => $existingContext->getId()];
            } else {
                throw new \Exception("We could not find the related context.");
            }
        }

        if (!empty($data['contextCharacter'])) {
            $model['contextCharacter'] = [
                'contextCharacterType' => $data['contextCharacter']
            ];
        }

        if (!empty($data['contextDatingPeriod'])) {
            $contextDating = ['contextDatingPeriod' => []];
            $contextDating['contextDatingPeriod']['value'] = $data['contextDatingPeriod'];

            if (!empty($data['contextDatingPeriodPrecision'])) {
                $contextDating['contextDatingPeriod']['contextDatingPeriodPrecision'] = $data['contextDatingPeriodPrecision'];
            }

            if (!empty($data['contextDatingPeriodNature'])) {
                $contextDating['contextDatingPeriod']['contextDatingPeriodNature'] = $data['contextDatingPeriodNature'];
            }

            if (!empty($data['contextDatingPeriodMethod'])) {
                $contextDating['contextDatingTechnique'] = ['contextDatingPeriodMethod' => $data['contextDatingPeriodMethod']];
            }

            if (!empty($data['contextDatingRemark'])) {
                $contextDating['contextDatingRemark'] = $data['contextDatingRemark'];
            }

            $model['contextDating'] = $contextDating;
        }

        return $model;
    }

    /**
     * Validate and add a log if necessary
     *
     * @param array $data
     * @param int $index
     * @return bool
     */
    private function validate(array $data, int $index)
    {
        try {
            $this->containsAllRequiredInformation($data);
        } catch (\Exception $ex) {
            $this->addLog($index, 'Some required information is missing: ' . $ex->getMessage(), '', ['data' => $data], false);

            return false;
        }

        // If the ID is not a C0, it's needs to reference a different context as well
        if (strtoupper($data['id']) !== 'C0' && empty($data['relatedContext'])) {
            $this->addLog($index, 'There needs to be a related context for non C0 contexts.', '', ['data' => $data], false);

            return false;
        }

        return true;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    private function containsAllRequiredInformation(array $data)
    {
        foreach ($this->getRequiredFields() as $field) {
            $value = @$data[$field] ?? '';
            $value = trim($value);

            if (empty($value)) {
                throw new \Exception("The field $field is required but contained an empty value.");
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @return array
     */
    private function transformData($data)
    {
        $data['id'] = strtoupper($data['id']);
        $data['local_context_id'] = $data['id'];
        $data['id'] = Context::createInternalId($data['id'], $data['excavationId']); // Set the ID to the global unique context ID

        return $data;
    }

    /**
     * @return array
     */
    private function getRequiredFields()
    {
        return [
            'id',
            'excavationId',
        ];
    }
}

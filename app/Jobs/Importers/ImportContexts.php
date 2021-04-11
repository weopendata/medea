<?php


namespace App\Jobs\Importers;

use App\Repositories\ContextRepository;

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

        // The contextId is unique, check if the context already exists or not
        $existingContext = app(ContextRepository::class)->getByContextId($data['id']);

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
            $this->addLog($index, 'Something went wrong: ' . $ex->getMessage(), $action, ['data' => $data], false);

            \Log::info($ex->getTraceAsString());
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function createContextModel(array $data)
    {
        $model = [];
        $model['contextId'] = ['contextIdValue' => $data['id']];
        $model['contextLegacyId'] = ['contextLegacyIdValue' => array_get($data, 'legacyId')];
        $model['contextType'] = array_get($data, 'contextType');
        $model['contextInterpretation'] = array_get($data, 'contextInterpretation');

        if (!empty($data['relatedContext'])) {
            $existingContext = app(ContextRepository::class)->getByContextId($data['relatedContext']);

            if (!empty($existingContext)) {
                $model['context'] = ['id' => $existingContext->getId()];
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
     * @return array
     */
    private function getRequiredFields()
    {
        return [
            'id',
        ];
    }
}

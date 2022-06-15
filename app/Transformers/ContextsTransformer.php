<?php

namespace App\Transformers;

class ContextsTransformer extends Transformer
{
    /**
     * @param  array $contexts
     * @return array
     */
    public function transform(array $contexts): array
    {
        return collect($contexts)
            ->map(function ($excavation) {
                return $this->transformOutgoingContext($excavation);
            })
            ->toArray();
    }

    /**
     * @param  array $context
     * @return void
     */
    private function transformOutgoingContext(array $context): array
    {
        $propertyMapping = [
            'legacyId' => 'contextLegacyId.contextLegacyIdValue',
            'contextType' => 'contextType',
            'contextCharacter' => 'contextCharacter.contextCharacterType',
            'contextInterpretation' => 'contextInterpretation',
            'contextDatingPeriod' => 'contextDating.contextDatingPeriod',
            'contextDatingPeriodPrecision' => 'contextDating.contextDatingTechnique.contextDatingPeriodPrecision',
            'contextDatingPeriodNature' => 'contextDating.contextDatingTechnique.contextDatingPeriodNature',
            'contextDatingPeriodMethod' => 'contextDating.contextDatingTechnique.contextDatingPeriodMethod',
            'contextDatingRemark' => 'contextDating.contextDatingRemark',
            'relatedContext' => 'relatedContext'
        ];

        $excavationId = array_get($context, 'excavationId');
        $contextExcavationId = array_get($context, 'local_context_id');

        if (empty($excavationId) && !empty($context['internalId'])) {
            $pieces = explode('__', $context['internalId']);
            $excavationId = @$pieces[0];

            if (empty($contextExcavationId)) {
                $contextExcavationId = @$pieces[1];
            }
        }

        $relatedContext = '';

        if (!empty($context['relatedContext'])) {
            $pieces = explode('__', $context['relatedContext']);
            $relatedContext = array_pop($pieces);
        }

        $transformedContext = [
            'excavationId' => $excavationId,
            'contextId' => $contextExcavationId,
            'relatedContextId' => $relatedContext
        ];

        foreach ($propertyMapping as $key => $path) {
            $value = array_get($context, $path) ?? '';

            if (!is_string($value)) {
                $transformedContext[$key] = '';

                continue;
            }

            $transformedContext[$key] = $value;
        }

        unset($transformedContext['relatedContext']);

        return $transformedContext;
    }
}
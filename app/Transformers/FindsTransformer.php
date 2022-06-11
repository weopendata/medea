<?php

namespace App\Transformers;

class FindsTransformer extends Transformer
{
    /**
     * @param  array $finds
     * @return array
     */
    public function transform(array $finds): array
    {
        return collect($finds)
            ->map(function ($find) {
                return $this->transformOutgoingFind($find);
            })
            ->toArray();
    }


    /**
     * @param  array $find
     * @return array
     */
    private function transformOutgoingFind(array $find): array
    {
        $propertyMapping = [
            'findId' => 'identifier',
            'collection' => 'collectionTitle',
            'objectPeriod' => 'period',
            'objectCategory' => 'category',
            'objectMaterial' => 'material',
            'objectTechnique' => 'technique',
            'inscription' => 'insignia',
            'photographPath' => 'photograph',
            'findSpotLocality' => 'locality',
            'finderEmail' => 'email',
        ];

        foreach ($find as $property => $value) {
            if (!array_key_exists($property, $propertyMapping)) {
                continue;
            }

            $find[$propertyMapping[$property]] = $value;

            unset($find[$property]);
        }

        if (!empty($find['excavationLocality'])) {
            $find['locality'] = $find['excavationLocality'];
        }

        return array_except($find, $this->getOmittedProperties());
    }

    /**
     * @return array
     */
    private function getOmittedProperties(): array
    {
        return [
            'fts_description',
            'photographCaptionPresent',
        ];
    }
}
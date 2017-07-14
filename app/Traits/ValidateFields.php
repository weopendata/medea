<?php

namespace App\Traits;

use App\Repositories\ListValueRepository;

/**
 * Validates values that belong to a property
 * that has an "authority list" attached to it.
 *
 * An authority list is a list of values the property can have
 */
trait ValidateFields
{
    private $validationMapping = [
        'category' => 'ObjectCategoryAuthorityList',
        'findSpotType' => 'FindSpotTypeAuthorityList',
        'material' => 'MaterialAuthorityList',
        'nation' => 'ProductionClassificationRulerNationAuthorityList',
        'period' => 'ProductionClassificationPeriodAuthorityList',
        'technique' => 'ProductionTechniqueTypeAuthorityList',
    ];

    /**
     * Validate a certain value for a certain property
     *
     * @param  string      $property
     * @param  string      $value
     * @return string|bool
     */
    public function validate($property, $value)
    {
        $method = 'validate' . studly_case($property);

        // Fetch the list for the property
        $property = strtolower($property);

        // If there's no validation for the property, return true
        if (! array_key_exists($property, $this->validationMapping)) {
            return $value;
        }

        // Check if the value is in the authority list of the property, case insensitive
        $allowedValues = app(ListValueRepository::class)->makeAuthorityListForLabel($this->validationMapping[$property]);

        $result = array_search(strtolower($value), array_map('strtolower', $allowedValues));

        if ($result !== false) {
            // If it's present then return the value from the authority list, not the original value
            return $allowedValues[$result];
        }

        return false;
    }
}

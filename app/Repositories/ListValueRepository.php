<?php

namespace App\Repositories;

use Everyman\Neo4j\Client;

class ListValueRepository
{
    protected function getClient()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create a new client with user and password
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        return $client;
    }

    public function getFindTemplate()
    {
        /** A find exists of:
         *
         object:
         * technique
         * materials
         * dimensions
         * surface treatment
         * category
         * inscription type

         search area:
         * search area type

         find spot:
         * find spot type

         classification:
         * period
         * rulernation
         * date (century wise)
         * type

         collection:
         * type
         */

        $list = [];

        $client = $this->getClient();

        $properties = [
            'object' => [
                'technique' => 'ProductionTechniqueTypeAuthorityList',
                'objectMaterial' => 'MaterialAuthorityList',
                'dimension_type' => 'DimensionTypeAuthorityList',
                'dimension_unit' => 'DimensionUnitAuthorityList',
                'inscription' => 'InscriptionTypeAuthorityList',
                'category' => 'ObjectCategoryAuthorityList',
            ],

            'search_area' => [
                'type' => 'SearchAreaTypeAuthorityList'
            ],

            'find_spot' => [
                'type' => 'FindSpotTypeAuthorityList'
            ],

            'classification' => [
                'nation' => 'ProductionClassificationRulerNationAuthorityList',
                'period' => 'ProductionClassificationPeriodAuthorityList',
                'type' => 'ProductionClassificationTypeAuthorityList',
                'culturepeople' => 'ProductionClassificationCultureAuthorityList'
            ],

            'collection' => [
                'type' => 'CollectionTypeAuthorityList'
            ],
        ];

        foreach ($properties as $property => $property_list) {
            foreach ($property_list as $key => $list_label) {
                $label = $client->makeLabel($list_label);
                // This shouldn't fail, otherwise we're haven't seeded properly, so an exception in place.
                $authority_list = $label->getNodes()->current();

                if (empty($list[$property])) {
                    $list[$property] = [];
                }

                $list[$property][$key] = $authority_list->getProperty('values');
            }
        }

        return $list;
    }

    /**
     * Return the configured values for the authority list
     *
     * @param  string $listLabel The name of the authority list (label name)
     * @return array
     */
    public function makeAuthorityListForLabel($listLabel)
    {
        $label = $this->getClient()->makeLabel($listLabel);

        // This shouldn't fail, otherwise the application hasn't been seeded properly, so an exception in place.
        $authorityList = $label->getNodes()->current();

        return $authorityList->getProperty('values');
    }
}

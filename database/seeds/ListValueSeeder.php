<?php

use Illuminate\Database\Seeder;
use Everyman\Neo4j\Client;

/**
 * This class' sole purpose is to provide an array of values that can be
 * use to fill up selection or other fields in the MEDEA platform (e.g. materials, techniques, ...)
 */
class ListValueSeeder extends Seeder
{
    public function run()
    {
        $functions = get_class_methods($this);

        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create an admin
        $client = new Everyman\Neo4j\Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        $labels = [$client->makeLabel('E32'), $client->makeLabel('AuthorityDocument'), $client->makeLabel('MEDEA_NODE')];

        foreach ($functions as $function) {
            if (strpos($function, 'AuthorityList') !== false) {
                // Get the name of the authority document
                $label = str_replace('get', '', $function);
                $name_label = $client->makeLabel($label);
                $labels[] = $name_label;

                // Avoid duplicates
                $duplicates = $name_label->getNodes();

                foreach ($duplicates as $duplicate) {
                    $relationships = $duplicate->getRelationships();

                    foreach ($relationships as $rel) {
                        $rel->delete();
                    }

                    $this->command->info("Found duplicate for $label, deleting it first in order to reseed.");
                    $duplicate->delete();
                }

                $node = $client->makeNode();
                $node->save();

                $node->addLabels($labels);
                $node->setProperty('values', $this->$function());
                $node->save();

                $this->command->info("Seeded node, $label");
            }
        }
    }

    public function getMaterialAuthorityList()
    {
        return [
            "koperlegering",
            "ijzer",
            "lood",
            "tin, loodtin",
            "goud",
            "zilver",
        ];
    }

    public function getRoles()
    {
        return [
            "detectorist",
            "onderzoeker",
            "registrator",
            "expert",
        ];
    }

    public function getSearchAreaTypeAuthorityList()
    {
        return [
            "weide",
            "akker",
            "strand",
            "hof",
            "gestorte grond",
            "andere",
        ];
    }

    public function getFindSpotTypeAuthorityList()
    {
        return [
            "weide",
            "akker",
            "strand",
            "hof",
            "gestorte grond",
            "andere",
        ];
    }

    public function getObjectCategoryAuthorityList()
    {
        return [
            "munt",
            "mantelspeld",
            "vingerhoed",
            "muntgewicht",
            "gesp",
            "riemtong",
            "bijl",
            "pijlpunt",
        ];
    }

    public function getProductionClassificationCultureAuthorityList()
    {
        return [
            "Bronstijd",
            "IJzertijd",
            "Romeins",
            "middeleeuws",
            "postmiddeleeuws",
            "modern",
            "Wereldoorlog I",
            "Wereldoorlog II",
        ];
    }

    public function getDimensionTypeAuthorityList()
    {
        return [
            "lengte",
            "breedte",
            "diepte",
            "omtrek",
            "diameter",
            "gewicht",
        ];
    }

    public function getDimensionUnitAuthorityList()
    {
        return [
            "millimeter",
            "centimeter",
            "meter",
            "gram",
        ];
    }

    public function getInscriptionTypeAuthorityList()
    {
        return [
            "initialen",
            "handtekening",
        ];
    }

    public function getProductionTechniqueTypeAuthorityList()
    {
        return [
            "gesmeed",
            "gegoten",
            "gehamerd",
            "geslagen (enkel voor munten)",
        ];
    }

    public function getCollectionTypeAuthorityList()
    {
        return [
           "prive collectie",
           "heemkundige collectie",
           "museumcollectie",
           "bibliotheekcollectie",
           "archiefcollectie",
        ];
    }

    public function getProductionClassificationTypeAuthorityList()
    {
        return [
            "Thörle Gruppe X var b",
            "Type 2.3",
        ];
    }

    public function getProductionClassificationCenturyAuthorityList()
    {
        return [
            "1de  E. (0/99)",
            "2de  E. (100/199)",
            "3de  E. (200/299)",
            "4de  E. (300/399)",
            "5de  E. (400/499)",
            "6de  E. (500/599)",
            "7de  E. (600/699)",
            "8de  E. (700/799)",
            "9de  E. (800/899)",
            "10de E. (900/999)",
            "11de E. (1000/1099)",
            "12de E. (1100/1199)",
            "13de E. (1200/1299)",
            "14de E. (1300/1399)",
            "1ste E. v.Chr. (-0099/0001)",
            "2de E. v.Chr. (-0199/0100)",
            "3de E. v.Chr. (-0299/0200)",
        ];
    }

    public function getProductionClassificationRulerNationAuthorityList()
    {
        return [
            "Napoleon",
            "Filips de Schone",
            "Augustus",
            "Nero",
            "Franken",
            "Friezen",
        ];
    }
}

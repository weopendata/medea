<?php

use App\NodeConstants;
use App\Services\NodeService;
use Illuminate\Database\Seeder;

/**
 * This class' sole purpose is to provide an array of values that can be
 * use to fill up selection or other fields in the MEDEA platform (e.g. materials, techniques, ...)
 */
class ListValueSeeder extends Seeder
{
    private $listNames = [
        'MaterialAuthorityList',
        'Roles',
        'SearchAreaTypeAuthorityList',
        'FindSpotTypeAuthorityList',
        'ObjectCategoryAuthorityList',
        'ProductionClassificationPeriodAuthorityList',
        'DimensionTypeAuthorityList',
        'DimensionUnitAuthorityList',
        'InscriptionTypeAuthorityList',
        'ProductionTechniqueTypeAuthorityList',
        'CollectionTypeAuthorityList',
        'ProductionClassificationTypeAuthorityList',
        'ProductionClassificationCenturyAuthorityList',
        'ProductionClassificationRulerNationAuthorityList',
        'ProductionClassificationCultureAuthorityList',
    ];

    public function run()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create an admin user
        $client = new Everyman\Neo4j\Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        foreach ($this->listNames as $listName) {
            $labels = [$client->makeLabel('E32'), $client->makeLabel('AuthorityDocument'), $client->makeLabel('MEDEA_NODE')];

            $nameLabel = $client->makeLabel($listName);
            $labels[] = $nameLabel;

            // Avoid duplicates
            $duplicates = NodeService::getNodesForLabel($nameLabel);

            foreach ($duplicates as $duplicate) {
                $relationships = $duplicate->getRelationships();

                foreach ($relationships as $rel) {
                    $rel->delete();
                }

                $this->command->info("Found duplicate for $listName, deleting it first in order to reseed.");
                $duplicate->delete();
            }

            $node = $client->makeNode();
            $node->save();

            $node->addLabels($labels);
            $functionName = 'get' . $listName;

            $node->setProperty('values', $this->$functionName());
            $node->save();

            $this->command->info("Seeded node, $listName");
        }
    }

    public function getMaterialAuthorityList()
    {
        return [
            'koperlegering',
            'ijzer',
            'lood',
            'tin of loodtin',
            'goud',
            'zilver',
        ];
    }

    public function getRoles()
    {
        return [
            'detectorist',
            'onderzoeker',
            'registrator',
            'expert',
        ];
    }

    public function getSearchAreaTypeAuthorityList()
    {
        return [
            'weide',
            'akker',
            'strand',
            'tuin of erf',
            'gestorte grond',
            'andere',
        ];
    }

    public function getFindSpotTypeAuthorityList()
    {
        return [
            'weide',
            'akker',
            'strand',
            'tuin of erf',
            'gestorte grond',
            'andere',
        ];
    }

    public function getObjectCategoryAuthorityList()
    {
        return [
            'andere',
            'armband',
            'baar',
            'balansonderdeel',
            'bijl',
            'bit',
            'boekbeslag',
            'decoratief onderdeel van paardentuig',
            'gesp(onderdeel)',
            'geweerkogel',
            'gewicht',
            'halsring of -ketting',
            'hanger',
            'harnasonderdeel',
            'helm',
            'hengsel/handvat',
            'hoefijzer',
            'insigne',
            'kandelaar (of onderdeel)',
            'kledinghaak',
            'knoop',
            'lakenloodje',
            'lepel',
            'mantelspeld',
            'mes',
            'meubelbeslag',
            'munitie',
            'munt',
            'muntgewicht',
            'musketkogel',
            'muziekinstrument',
            'onbekend',
            'overig gereedschap',
            'pelgrimsampul',
            'pijlpunt',
            'pincet',
            'rekenpenning',
            'riem- en leerbeslag',
            'riemtong',
            'schedebeslag',
            'schildknop',
            'sleutel',
            'slot (of onderdeel)',
            'speelgoed',
            'speelstuk',
            'speerpunt',
            'speld/naald',
            'spoor',
            'stijgbeugel',
            'stylus',
            'vaatwerk',
            'vingerhoed',
            'vingerring',
            'vuurwapenonderdeel of -accessoire',
            'zegelstempel',
            'zwaard(onderdeel)',
        ];
    }

    public function getProductionClassificationPeriodAuthorityList()
    {
        return [
            'Bronstijd',
            'IJzertijd',
            'Romeins',
            'middeleeuws',
            'postmiddeleeuws',
            'modern',
            'onbekend',
            'Wereldoorlog I',
            'Wereldoorlog II',
        ];
    }

    public function getProductionClassificationCultureAuthorityList()
    {
        return [
            'Bronstijd',
            'IJzertijd',
            'Romeins',
            'middeleeuws',
            'postmiddeleeuws',
            'modern',
            'Wereldoorlog I',
            'Wereldoorlog II',
        ];
    }

    public function getDimensionTypeAuthorityList()
    {
        return [
            'lengte',
            'breedte',
            'diepte',
            'omtrek',
            'diameter',
            'gewicht',
        ];
    }

    public function getDimensionUnitAuthorityList()
    {
        return [
            'millimeter',
            'centimeter',
            'meter',
            'gram',
        ];
    }

    public function getInscriptionTypeAuthorityList()
    {
        return [
            'initialen',
            'handtekening',
        ];
    }

    public function getProductionTechniqueTypeAuthorityList()
    {
        return [
            'gesmeed',
            'gegoten',
            'gehamerd',
            'geslagen (enkel voor munten)',
        ];
    }

    public function getCollectionTypeAuthorityList()
    {
        return [
            'Fysieke collectie van instelling of vereniging',
            'Gecentraliseerde registratie van detectievondsten',
            'Kortstondig registratieproject',
        ];
    }

    public function getProductionClassificationTypeAuthorityList()
    {
        return [
            'Thörle Gruppe X var b',
            'Type 2.3',
        ];
    }

    public function getProductionClassificationCenturyAuthorityList()
    {
        return [
            '1de  E. (0/99)',
            '2de  E. (100/199)',
            '3de  E. (200/299)',
            '4de  E. (300/399)',
            '5de  E. (400/499)',
            '6de  E. (500/599)',
            '7de  E. (600/699)',
            '8de  E. (700/799)',
            '9de  E. (800/899)',
            '10de E. (900/999)',
            '11de E. (1000/1099)',
            '12de E. (1100/1199)',
            '13de E. (1200/1299)',
            '14de E. (1300/1399)',
            '1ste E. v.Chr. (-0099/0001)',
            '2de E. v.Chr. (-0199/0100)',
            '3de E. v.Chr. (-0299/0200)',
        ];
    }

    public function getProductionClassificationRulerNationAuthorityList()
    {
        return [
            'Napoleon',
            'Filips de Schone',
            'Augustus',
            'Nero',
            'Franken',
            'Friezen',
        ];
    }
}

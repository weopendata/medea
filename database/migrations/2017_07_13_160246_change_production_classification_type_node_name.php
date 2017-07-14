<?php

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Illuminate\Database\Migrations\Migration;

class ChangeProductionClassificationTypeNodeName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create a new client with user and password
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        $queryString = 'MATCH (n:productionClassificationMainType) return n';

        $query = new Query($client, $queryString);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            $newLabel = $client->makeLabel('productionClassificationType');
            $oldLabel = $client->makeLabel('productionClassificationMainType');

            $result['value']->setProperty('name', 'productionClassificationType');
            $result['value']->addLabels([$newLabel]);
            $result['value']->removeLabels([$oldLabel]);
            $result['value']->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

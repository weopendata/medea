<?php

use Illuminate\Database\Migrations\Migration;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;

class FixEmbargoValues extends Migration
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
        $this->client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $this->client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        $queryString = 'MATCH (n:E22) return n';

        $query = new Query($this->client, $queryString);

        $results = $query->getResultSet();

        foreach ($results as $result) {
            $objectNode = $result['value'];

            if ($objectNode->getProperty('embargo') === false) {
                $objectNode->setProperty('embargo', 'false');
                $objectNode->save();
            } elseif ($objectNode->getProperty('embargo') === true) {
                $objectNode->setProperty('embargo', 'true');
                $objectNode->save();
            }
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

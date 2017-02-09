<?php

use Illuminate\Database\Migrations\Migration;
use Everyman\Neo4j\Client;
use App\Models\Object;
use Everyman\Neo4j\Cypher\Query;

class AddFieldsToFtsField extends Migration
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

            $object = new Object();
            $object->setNode($objectNode);
            $object->computeFtsField();
        }
        die;
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

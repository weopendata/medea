<?php

use Illuminate\Database\Migrations\Migration;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use App\Models\Person;

class ChangeProfileAccessLevel extends Migration
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

        $queryString = 'MATCH (n:person) return n';

        $query = new Query($client, $queryString);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            $personNode = $result['value'];

            // Get the new status based on a status mapping from old to new strings
            $newAccessLevel = $this->getNewProfileAccessLevel($personNode->profileAccessLevel);

            $personNode->profileAccessLevel = $newAccessLevel;
            $personNode->save();
        }
    }

    private function getNewProfileAccessLevel($accessLevel)
    {
        if ($accessLevel == 1 || $accessLevel == 2) {
            return 0;
        }

        return $accessLevel;
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

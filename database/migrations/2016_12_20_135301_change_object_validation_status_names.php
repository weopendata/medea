<?php

use Illuminate\Database\Migrations\Migration;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;

class ChangeObjectValidationStatusNames extends Migration
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

        $queryString = 'MATCH (n:objectValidationStatus) return n';

        $query = new Query($client, $queryString);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            $objectValidationNode = $result['value'];

            // Get the new status based on a status mapping from old to new strings
            $newStatus = $this->getNewStatus($objectValidationNode->value);

            $objectValidationNode->value = $newStatus;
            $objectValidationNode->save();
        }
    }

    private function getNewStatus($oldStatus)
    {
        switch ($oldStatus) {
            case 'gevalideerd':
                return 'Gepubliceerd';
                break;
            case 'voorlopig':
                return 'Voorlopige versie';
                break;
            case 'revisie nodig':
                return 'Aan te passen';
                break;
            case 'embargo':
                return 'Afgeschermd';
                break;
            case 'afgekeurd':
                return 'Wordt verwijderd';
                break;
            case 'in bewerking':
                return 'Klaar voor validatie';
                break;
            default:
                return $oldStatus;
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

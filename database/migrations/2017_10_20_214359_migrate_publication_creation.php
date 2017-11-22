<?php

use Illuminate\Database\Migrations\Migration;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;

class MigratePublicationCreation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // NOTE: this is a data migration and has nothing to do with migrating to a new data structure
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create a new client with user and password
        $this->client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $this->client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        $this->migratePublicationTimes();
        $this->migratePublicationLocations();
        $this->cleanUp();
    }

    private function migratePublicationTimes()
    {
        $queryString = 'MATCH (publication:E31)-[rel:P94]->(publicationCreation:E65)-[p4:P4]->(time:E53)
            OPTIONAL MATCH (publication)-[P94]->(leftoverPubCreation:E65)-[P14]->(actor:E39)
            WHERE NOT (publicationCreation)-[P14]->()
            return publication, rel, publicationCreation, time, actor, leftoverPubCreation, p4';

        $query = new Query($this->client, $queryString);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            if (empty($result['actor'])) {
                echo 'No actor found for ' . $result['publicationCreation']->getId() . PHP_EOL;
                continue;
            }

            // If one of the creations does not have an actor attached to it, add it to the node that is a creation with an actor
            $leftoverPubCreation = $result['leftoverPubCreation'];
            $timeNode = $result['time'];

            $timeNode->removeLabels([$this->makeLabel('E53')]);
            $timeNode->addLabels([$this->makeLabel('E52')]);

            $leftoverPubCreation->relateTo($timeNode, 'P4')->save();

            $result['p4']->delete();

            echo $timeNode->getId() . PHP_EOL;
        }
    }

    private function migratePublicationLocations()
    {
        $queryString = 'MATCH (publication:E31)-[rel:P94]->(publicationCreation:E65)-[p7:P7]->(location:E53)
            OPTIONAL MATCH (publication)-[P94]->(leftoverPubCreation:E65)-[P14]->(actor:E39)
            WHERE NOT (publicationCreation)-[P14]->()
            return publication, rel, publicationCreation, location, actor, leftoverPubCreation, p7';

        $query = new Query($this->client, $queryString);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            if (empty($result['actor'])) {
                echo 'No actor found for ' . $result['publicationCreation']->getId() . PHP_EOL;
                continue;
            }

            // If one of the creations does not have an actor attached to it, add it to the node that is a creation with an actor
            $leftoverPubCreation = $result['leftoverPubCreation'];
            $locationNode = $result['location'];

            $leftoverPubCreation->relateTo($locationNode, 'P7')->save();

            $result['p7']->delete();

            echo $locationNode->getId() . PHP_EOL;
        }
    }

    private function cleanUp()
    {
        $queryString = 'MATCH (publication:E31)-[rel:P94]->(publicationCreation:E65)
            WHERE NOT (publicationCreation)-[]->()
            return publicationCreation, publication, rel';

        $query = new Query($this->client, $queryString);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            $result['rel']->delete();
            $result['publicationCreation']->delete();

            echo 'Deleted: ' . $result['publicationCreation']->getId() . PHP_EOL;
        }
    }

    /**
     * Create and return a label
     *
     * @param  string $labelName
     * @return Label
     */
    private function makeLabel($labelName)
    {
        return $this->client->makeLabel($labelName);
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

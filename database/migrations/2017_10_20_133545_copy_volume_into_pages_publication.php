<?php

use Illuminate\Database\Migrations\Migration;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;

class CopyVolumeIntoPagesPublication extends Migration
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

        $queryString = 'MATCH (publication:E31)-[volumeRelationship:P3]->(volume:E62) return publication, volume, volumeRelationship';

        $query = new Query($this->client, $queryString);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            $publication = $result['publication'];
            $volume = $result['volume'];

            // Get the volumes value that is actually a
            $pagesValue = $volume->getProperty('value');

            // Add a Pages node with the volume value
            $node = $this->client->makeNode();
            $node->setProperty('name', 'publicationPages');
            $node->save();

            $node->setProperty('value', $pagesValue);
            $node->save();

            // Set the proper labels to the objectDimensionType
            $node->addLabels([$this->makeLabel('E62'), $this->makeLabel('publicationPages'), $this->makeLabel($publication->getProperty('MEDEA_UUID'))]);

            // Delete the obsolete node
            $result['volumeRelationship']->delete();
            $volume->delete();
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

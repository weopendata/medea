<?php

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use App\Models\ProductionClassification;
use Illuminate\Database\Migrations\Migration;

class AddMainTypeToProductionClassifications extends Migration
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

        $queryString = 'MATCH (n:E17) return n';

        $query = new Query($this->client, $queryString);

        $results = $query->getResultSet();

        foreach ($results as $result) {
            $classificationNode = $result['value'];

            // Check if the node has a connection with a main type
            // if not, add a default main type
            if (! $this->doesClassificationHasMainType($classificationNode->getId())) {
                $productionClassification = new ProductionClassification();
                $productionClassification->setNode($classificationNode);

                $mainTypeNode = $this->makeMainTypeNode($productionClassification->getGeneralId());

                $classificationNode->relateTo($mainTypeNode, 'P2')->save();
            }
        }
    }

    /**
     * Create a productionClassificationMainTypeNode based on a number of relevant labels
     *
     * @param  string $generalId
     * @return Node
     */
    private function makeMainTypeNode($generalId)
    {
        $node = $this->client->makeNode();
        $node->setProperty('name', 'productionClassificationMainType');
        $node->save();

        $node->setProperty('value', 'Typologie');
        $node->save();

        // Set the proper labels to the objectDimensionType
        $node->addLabels([$this->makeLabel('E55'), $this->makeLabel('productionClassificationMainType'), $this->makeLabel($generalId)]);

        return $node;
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
     * Check if a classificatoin has a relationship with a node called "productionClassificationMainType"
     *
     * @param  integer $classificationId
     * @return boolean
     */
    private function doesClassificationHasMainType($classificationId)
    {
        $query = 'MATCH (n)-[P2]-(type:E55{name:"productionClassificationMainType"}) where id(n)=' . $classificationId . ' return n';

        $query = new Query($this->client, $query);

        $results = $query->getResultSet();

        return $results->count() > 0;
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

<?php

namespace App\Models;

use Everyman\Neo4j\Client;

class Base
{
    protected $node;

    protected $unique_identifer = "MEDEA_UUID";

    /**
     * Get the Neo4j client
     *
     * @return
     */
    protected static function getClient()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create an admin
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        return $client;
    }

    /**
     * Create a label (Everyman\Neo4j\Label)
     *
     * @return Label
     */
    protected static function makeLabel($label)
    {
        $client = self::getClient();

        return $client->makeLabel($label);
    }

    public function __construct($node)
    {
        $this->node = $node;

        // Identify all related nodes with the main node (being Object)
        // Eases the way in which we can perform a delete (and find)
        $general_id = "MEDEA" . sha1(time() . "__" . time());

        $this->node->setProperty($this->unique_identifer, $general_id);
    }

    protected function getGeneralId()
    {
        return $this->node->getProperty($this->unique_identifer);
    }

    protected static function createNode($properties = [])
    {
        $client = self::getClient();

        $node = $client->makeNode();

        $properties = array_only($properties, static::$fillable);

        foreach ($properties as $key => $val) {
            $node->setProperty($key, $val);
        }

        return $node;
    }

    public function save()
    {
        $this->node->save();

        $cidocLabel = self::makeLabel(static::$NODE_TYPE);
        $humanLabel = self::makeLabel(static::$NODE_NAME);
        $medeaLabel = self::makeLabel('MEDEA_NODE');

        $this->node->addLabels([$cidocLabel, $humanLabel, $medeaLabel]);
    }

    public function getNode()
    {
        return $this->node;
    }

    /**
     * Delete node and all accompanied relationships
     */
    public function delete()
    {
        $relationships = $this->node->getRelationships();

        foreach ($relationships as $relationship) {
            $relationship->delete();
        }

        $this->node->delete();
    }

    /**
     * Connect two nodes through relationship
     * note that these are unidirectional
     *
     * @param $end_model     Model   The model that's on the receiving end of the relationship
     * @param $relationship string Name of the relationship
     *
     * @return void
     */
    public function makeRelationship($end_model, $relationship)
    {
        $this->node->relateTo($end_model->getNode(), $relationship)->save();
    }
}

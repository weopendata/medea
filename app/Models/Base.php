<?php

namespace App\Models;

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Relationship;

class Base
{
    protected $node;

    protected $unique_identifer = "MEDEA_UUID";

    /* List the related models (that are 1 level deep) with their respective relationship_name, this way we can cascade CRUD more eloquently */
    protected $relatedModels = [
    ];

    protected $implicitModels = [
    ];

    protected $lazy_deletion = false;

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

    public function __construct($properties = [])
    {
        if (!empty($properties)) {
            $client = self::getClient();

            $this->node = $client->makeNode();
            // Identify all related nodes with the main node (being Object)
            // Eases the way in which we can perform a delete (and find)
            $general_id = "MEDEA" . sha1(time() . "__" . time());

            $this->node->setProperty($this->unique_identifer, $general_id)->save();
            $this->node->save();

            // Initiate model relationship cascading that are one level deep
            foreach ($this->relatedModels as $relationship_name => $config) {
                $input = $properties[$config['key']];

                if (!empty($input)) {
                    $model_name = '\App\Models\\' . $config['model_name'];
                    $model = new $model_name($input);
                    $model->save();

                    $this->makeRelationship($model, $relationship_name);
                }
            }

            foreach ($this->implicitModels as $relationship_name => $config) {
                $input = $properties[$config['key']];

                if (!empty($input)) {
                    if (is_array($input)) {
                        foreach ($input as $entry) {
                            $create_function = 'create' . $config['object'];
                            $related_node = $this->$create_function($entry);

                            // Make the relationship
                            $this->node->relateTo($related_node, $relationship_name)->save();
                        }
                    } else {
                        $create_function = 'create' . $config['object'];
                        $related_node = $this->$create_function($input);

                        // Make the relationship
                        $this->node->relateTo($related_node, $relationship_name)->save();
                    }
                }
            }
        }

    }

    protected function getGeneralId()
    {
        return $this->node->getProperty($this->unique_identifer);
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

    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * Delete node and all accompanied relationships
     * Take into account the explicit relationships that need cascading deletion
     */
    public function delete()
    {
        foreach ($this->relatedModels as $relationship_name => $config) {
            if ($config['cascade_delete']) {
                $relationships = $this->node->getRelationships([$relationship_name], Relationship::DirectionOut);
                $model_name = 'App\Models\\' . $config['model_name'];

                foreach ($relationships as $relationship) {
                    $end_node = $relationship->getEndNode();

                    $model = new $model_name();
                    $model->setNode($end_node);
                    $model->delete();
                }
            }
        }

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

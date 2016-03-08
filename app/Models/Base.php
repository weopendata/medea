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
            $general_id = "MEDEA" . sha1(str_random(10) . "__" . time());

            $this->node->setProperty($this->unique_identifer, $general_id)->save();
            $this->node->save();

            // Initiate model relationship cascading that are one level deep
            foreach ($this->relatedModels as $relationship_name => $config) {
                // Check if the related model is required
                if (empty($properties[$config['key']]) && (empty($config['required']) || $config['required'])) {
                    \App::abort(400, "The property '" . $config['key'] . "'' is required in order to create the model '" . static::$NODE_NAME ."'");
                }
                $input = $properties[$config['key']];

                if (!empty($input)) {
                    $model_name = 'App\Models\\' . $config['model_name'];
                    $model = new $model_name($input);
                    $model->save();

                    $this->makeRelationship($model, $relationship_name);
                }
            }

            foreach ($this->implicitModels as $relationship_name => $config) {
                $input = $properties[$config['key']];

                if (!empty($input)) {
                    if (is_array($input) && !$this->isAssoc($input)) {
                        foreach ($input as $entry) {
                            $related_node = $this->createImplicitNode($entry, $config, $general_id);

                            // Make the relationship
                            $this->node->relateTo($related_node, $relationship_name)->save();
                        }
                    } else {
                        $related_node = $this->createImplicitNode($input, $config, $general_id);

                        // Make the relationship
                        $this->node->relateTo($related_node, $relationship_name)->save();
                    }
                }
            }
        }
    }

    private function createImplicitNode($input, $config, $general_id)
    {
        $client = self::getClient();

        // If the variable value_node is set, this means a simple creation of a node is
        // viable and can be automated. If not the specific create function will be called
        // to create the further internal model. Basically this means we only need to do
        // a one to one translation from a value and a node -> make the node and set the value property.
        if (!empty($config['value_node']) && $config['value_node']) {
            $related_node = $this->createValueNode([$config['cidoc_type']], $input);
        } else {
            $create_function = 'create' . $config['object'];
            $related_node = $this->$create_function($input);
        }

        return $related_node;
    }

    protected function getGeneralId()
    {
        return $this->node->getProperty($this->unique_identifer);
    }

    public function save()
    {
        $this->node->save();

        $cidoc_label = self::makeLabel(static::$NODE_TYPE);
        $human_label = self::makeLabel(static::$NODE_NAME);
        $medea_label = self::makeLabel('MEDEA_NODE');

        $this->node->addLabels([$cidoc_label, $human_label, $medea_label]);
    }

    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set the Everyman Node instance of the object
     *
     * @param $node Node
     *
     * @return void
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * Delete node and all accompanied relationships
     * Take into account the explicit relationships that need cascading deletion
     *
     * @return void
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

    /**
     * Create a node instance with a value property,
     * the general id of the invoking class is automatically added as a label
     *
     * @param $labels array      Array of strings that need to be added to the node as labels
     * @param $value  int|string Int or string that contains the value of the node
     *
     * @return Node
     */
    protected function createValueNode($labels, $value)
    {
        $client = self::getClient();

        $general_id = $this->getGeneralId();

        $node = $client->makeNode();
        $node->save();

        $node_labels = [self::makeLabel($general_id)];

        foreach ($labels as $label) {
            $node_labels[] = self::makeLabel($label);
        }

        $node->addLabels($node_labels);
        $node->setProperty('value', $value);
        $node->save();

        return $node;
    }

    private function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

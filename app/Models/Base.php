<?php

namespace App\Models;

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Relationship;

class Base
{
    protected $node;

    protected $has_unique_id = false;

    protected $unique_identifer = "MEDEA_UUID";

    /**
     * List the related models (that are 1 level deep) with their respective relationship_name,
     * this way we can cascade CRUD more eloquently
     */
    protected $relatedModels = [
    ];

    /**
     *
     * List the models that need to be created implicitly, they don't exist in the sense of Model classes
     * These nodes are only relevant to a node that has been modeled in a Model class
     */
    protected $implicitModels = [
    ];

    /**
     * List of the properties of the model that
     * should be added as a property on the node
     */
    protected $properties = [
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

            // Set value properties for the node
            foreach ($this->properties as $property_config) {
                $property_name = $property_config['name'];

                if (!empty($properties[$property_name])) {
                    $this->node->setProperty($property_name, $properties[$property_name]);
                } elseif (array_key_exists('default_value', $property_config)) {
                    $this->node->setProperty($property_name, $property_config['default_value']);
                }
            }

            $this->node->save();

            // Initiate model relationship cascading that are one level deep
            foreach ($this->relatedModels as $relationship_name => $config) {
                // Check if the related model is required
                if (empty($properties[$config['key']]) && @$config['required']) {
                    \App::abort(400, "The property '" . $config['key'] . "'' is required in order to create the model '" . static::$NODE_NAME ."'");

                } elseif (!empty($properties[$config['key']])) {
                    $input = $properties[$config['key']];

                    if (!empty($input)) {
                        if (is_array($input) && !$this->isAssoc($input)) {
                            foreach ($input as $entry) {
                                $model_name = 'App\Models\\' . $config['model_name'];
                                $model = new $model_name($entry);
                                $model->save();

                                $this->makeRelationship($model, $relationship_name);
                            }
                        } else {
                            if (!empty($config['link_only']) && $config['link_only']) {
                                // Fetch the node and create the relationship
                                $model = $this->searchNode($input['id'], $config['model_name']);
                            } else {
                                $model_name = 'App\Models\\' . $config['model_name'];
                                $model = new $model_name($input);
                                $model->save();
                            }

                            if (!empty($model)) {
                                $this->makeRelationship($model, $relationship_name);

                                if (!empty($config['reverse_relationhip'])) {
                                    $model->getNode()->relateTo($this->node, $config['reverse_relationhip'])->save();
                                }
                            }
                        }
                    }
                }
            }

            foreach ($this->implicitModels as $config) {
                $relationship = $config['relationship'];
                $model_config = $config['config'];

                $input = @$properties[$model_config['key']];

                if (!empty($input)) {
                    // We can have multiple instances of an implicit node (e.g. multiple dimensions)
                    // Check which of the cases it is by checking whether the array is associative or not
                    if (is_array($input) && !$this->isAssoc($input)) {
                        foreach ($input as $entry) {
                            $related_node = $this->createImplicitNode($entry, $model_config, $general_id);

                            // Make the relationship
                            $this->node->relateTo($related_node, $relationship)->save();
                        }
                    } else {
                        $related_node = $this->createImplicitNode($input, $model_config, $general_id);

                        // Make the relationship
                        $this->node->relateTo($related_node, $relationship)->save();
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
            $related_node = $this->createValueNode($config['key'], [$config['cidoc_type']], $input);
        } else {
            $create_function = 'create' . ucfirst($config['name']);
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

        if ($this->has_unique_id) {
            // Add an ID to the node
            $id_name = lcfirst(static::$NODE_NAME) . 'Id';
            $id_node = $id_node = $this->createValueNode('identifier', ['E42', $id_name], $this->node->getId());

            $this->node->relateTo($id_node, 'P1')->save();
        }
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
     * @param $end_model    Model  The model that's on the receiving end of the relationship
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
    protected function createValueNode($name, $labels, $value)
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
        $node->setProperty('name', $name);
        $node->save();

        return $node;
    }

    /**
     * Recursively retrieve the data out of related nodes
     *
     * @param Node
     *
     * @return array
     */
    public function getValues()
    {
        $data = [];

        // Ask all of the values of the related models
        foreach ($this->node->getRelationships(array_keys($this->relatedModels), Relationship::DirectionOut) as $relationship) {
            if (!empty($this->relatedModels)) {
                $end_node = $relationship->getEndNode();
                $model_name = 'App\Models\\' . $this->relatedModels[$relationship->getType()]['model_name'];

                $related_model = new $model_name();
                $related_model->setNode($end_node);

                if (!empty($this->relatedModels[$relationship->getType()]['plural']) && $this->relatedModels[$relationship->getType()]['plural']) {
                    if (empty($data[$this->relatedModels[$relationship->getType()]['key']])) {
                        $data[$this->relatedModels[$relationship->getType()]['key']] = [];
                    }

                    $data[$this->relatedModels[$relationship->getType()]['key']][] = $related_model->getValues();
                } else {
                    $data[$this->relatedModels[$relationship->getType()]['key']] = $related_model->getValues();
                }
            }
        }

        // Ask of the values of the implicit models
        // Because we don't use the relationship as a unique key in the implicit models array (duplicates occur in the data model)
        // We need to inverse map the name on to the config, which is unique ( <=> relationship)
        $model_map = $this->getImplicitModelMap();
        $implicit_relationships = $this->getImplicitRelationships();

        // Add the computed identifier relationship by default
        $implicit_relationships[] = 'P1';

        foreach ($this->node->getRelationships($implicit_relationships, Relationship::DirectionOut) as $relationship) {
            if (!empty($this->implicitModels)) {
                $end_node = $relationship->getEndNode();
                $node_name = $end_node->getProperty('name');

                // Parse value nodes
                if (!empty($end_node->getProperty('value'))) {
                    if (!empty($model_map[$node_name]['plural']) && $model_map[$node_name]['plural']) {
                        if (empty($data[$node_name])) {
                            $data[$node_name] = [];
                        }

                        $data[$node_name][] = $end_node->getProperty('value');
                    } else {
                        $data[$node_name] = $end_node->getProperty('value');
                    }
                } else {
                    // Parse non-value nodes
                    // Check for duplicate relationships (= build an array of values)
                    if (!empty($model_map[$node_name]['plural']) && $model_map[$node_name]['plural']) {
                        if (empty($data[$node_name])) {
                            $data[$node_name] = [];
                        }
                        $data[$node_name][] = $this->getImplicitValues($end_node);
                    } else {
                        $data[$node_name] = $this->getImplicitValues($end_node);
                    }
                }
            }
        }

        // Get the data properties
        foreach ($this->properties as $property) {
            $val = $this->node->getProperty($property['name']);

            if (!is_null($val)) {
                $data[$property['name']] = $val;
            }
        }

        return $data;
    }

    /**
     * Search a node and retrieve the Node object
     * this is necessary for example when the base node doesn't need
     * to instantiate new nodes, but rather needs to link with existing ones
     *
     * @param integer $node_id
     * @param string  $model
     *
     * @return null|Node
     */
    private function searchNode($node_id, $model)
    {
        $client = self::getClient();

        $node = $client->getNode($node_id);

        if (empty($node)) {
            return [];
        }

        foreach ($node->getLabels() as $label) {
            // We can use the model name as a label because
            // the models that we fetch are all related models
            // meaning they have cidoc labels and model name labels
            if ($label->getName() == $model) {
                 $model_name = 'App\Models\\' . $model;
                 $model = new $model_name();
                 $model->setNode($node);

                 return $model;
            }
        }

        return null;
    }

    private function getImplicitValues($implicit_node)
    {
        $data = [];

        foreach ($implicit_node->getRelationships([], Relationship::DirectionOut) as $relationship) {
            $end_node = $relationship->getEndNode();

            if (!empty($end_node->getProperty('value'))) {
                $data[$end_node->getProperty('name')] = $end_node->getProperty('value');
            } else {
                if (!empty($data[$end_node->getProperty('name')])) {
                    $tmp = $data[$end_node->getProperty('name')];
                    $data[$end_node->getProperty('name')] = [$tmp];
                    $data[$end_node->getProperty('name')][] = $this->getImplicitValues($end_node);
                } else {
                    $data[$end_node->getProperty('name')] = $this->getImplicitValues($end_node);
                }
            }
        }

        return $data;
    }

    private function getImplicitRelationships()
    {
        $relationships = [];

        foreach ($this->implicitModels as $model_config) {
            $relationships[] = $model_config['relationship'];
        }

        return $relationships;
    }

    private function getImplicitModelMap()
    {
        $model_map = [];

        foreach ($this->implicitModels as $model_config) {
            $model_map[$model_config['config']['key']] = $model_config['config'];
        }

        return $model_map;
    }

    private function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

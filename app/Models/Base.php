<?php

namespace App\Models;

use App\Repositories\BaseRepository;
use App\Services\NodeService;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Relationship;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Exception;
use Carbon\Carbon;
use PhpParser\Node;

/**
 * The base class for a node in the graph
 * Because this class is almost inherently complex
 */
class Base
{
    protected $node;

    protected $hasUniqueId = true;

    protected $uniqueIdentifier = 'MEDEA_UUID';

    /**
     * List of related models (that are 1 level deep) with their respective relationshipName,
     * this way we can cascade CRUD more eloquently
     *
     * @var $relatedModels
     */
    protected $relatedModels = [
    ];

    /**
     *
     * List the models that need to be created implicitly, they don't exist in the sense of Model classes
     * These nodes are only relevant to a node that has been modeled in a Model class
     *
     * @var $implicitModels
     */
    protected $implicitModels = [
    ];

    /**
     * List of the properties of the model that
     * should be added as a property on the node
     *
     * @var $properties
     */
    protected $properties = [
    ];

    protected $lazyDeletion = false;

    protected $timestamps = true;

    /**
     * Get the Neo4j client
     *
     * @return Client
     */
    protected static function getClient()
    {
        $neo4jConfig = \Config::get('database.connections.neo4j');

        // Create an admin
        $client = new Client($neo4jConfig['host'], $neo4jConfig['port']);
        $client->getTransport()->setAuth($neo4jConfig['username'], $neo4jConfig['password']);

        return $client;
    }

    /**
     * Create a label (Everyman\Neo4j\Label)
     *
     * @param  string $label
     * @return Label
     */
    protected static function makeLabel($label)
    {
        $client = self::getClient();

        return $client->makeLabel($label);
    }

    /**
     * Base constructor.
     * @param array $properties
     * @throws Exception
     */
    public function __construct($properties = [])
    {
        if (empty($properties)) {
            return;
        }

        $this->node = NodeService::makeNode();

        $generalId = $this->createMedeaId();

        $this->node->setProperty($this->uniqueIdentifier, $generalId)->save();
        $this->node->setProperty('name', static::$NODE_NAME)->save();

        // Set the properties of the model
        $this->setProperties($properties);

        $this->node->save();

        // Check if timestamps need to be added
        // if so, add created_at and updated_at
        if ($this->timestamps) {
            $this->addTimestamps();
        }

        // Create related models through recursion
        // these models need to be consistent, meaning once they are created
        // they need to have the same URI throughout the life cycle
        foreach ($this->relatedModels as $relationshipName => $config) {
            // Check if the related model is required
            if (empty($properties[$config['key']]) && @$config['required']) {
                abort(400, "The property '" . $config['key'] .
                    "'' is required in order to create the model '" . static::$NODE_NAME . "'");

            } elseif (! empty($properties[$config['key']])) {
                $input = $properties[$config['key']];

                if (! empty($input)) {
                    $model = null;

                    if (is_array($input) && ! $this->isAssoc($input)) {
                        foreach ($input as $entry) {
                            $model_name = 'App\Models\\' . $config['model_name'];
                            $model = new $model_name($entry);
                            $model->save();

                            if (! empty($model)) {
                                $this->makeRelationship($model, $relationshipName);

                                if (! empty($config['reverse_relationship'])) {
                                    $model->getNode()->relateTo($this->node, $config['reverse_relationship'])->save();
                                }
                            }
                        }
                    } else {
                        if (! empty($config['link_only']) && $config['link_only']) {
                            // Fetch the node and create the relationship
                            $model = $this->searchNode($input['id'], $config['model_name']);
                        } else {
                            $model_name = 'App\Models\\' . $config['model_name'];
                            $model = new $model_name($input);
                            $model->save();
                        }

                        if (! empty($model)) {
                            $this->makeRelationship($model, $relationshipName);

                            if (! empty($config['reverse_relationship'])) {
                                $model->getNode()->relateTo($this->node, $config['reverse_relationship'])->save();
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

            if (! empty($input)) {
                // We can have multiple instances of an implicit node (e.g. multiple dimensions)
                // Check which of the cases it is by checking whether the array is associative or not
                if (is_array($input) && ! $this->isAssoc($input)) {
                    foreach ($input as $entry) {
                        $related_node = $this->createImplicitNode($entry, $model_config);

                        if (! empty($related_node)) {
                            // Make the relationship
                            $this->node->relateTo($related_node, $relationship)->save();
                        }
                    }
                } else {
                    $related_node = $this->createImplicitNode($input, $model_config);

                    if (! empty($related_node)) {
                        // Make the relationship
                        $this->node->relateTo($related_node, $relationship)->save();
                    }
                }
            }
        }
    }

    /**
     * Update a model with its related nodes
     * This function expects a node to be already set to the model
     *
     * @param array $properties
     *
     * @return Node
     */
    public function update($properties)
    {
        if (! empty($properties)) {
            $client = self::getClient();

            $this->setProperties($properties);

            // Create related models through recursion
            foreach ($this->relatedModels as $relationshipName => $config) {
                // Check if the related model is required
                if (empty($properties[$config['key']]) && @$config['required']) {
                    abort(400, "The property '" . $config['key'] . "'' is required in order to create the model '" . static::$NODE_NAME . "'");

                } elseif (! empty($properties[$config['key']])) {
                    $input = $properties[$config['key']];

                    // Keep track of the related models by the returned identifiers
                    // The identifiers that we get and are not in this list, we need to delete
                    $related_identifiers = [];

                    if (! empty($input)) {
                        if (is_array($input) && ! $this->isAssoc($input)) {
                            foreach ($input as $entry) {
                                // Check if an identifier is provided, if not, perform a create
                                if (empty($entry['identifier'])) {
                                    $model_name = 'App\Models\\' . $config['model_name'];
                                    $model = new $model_name($entry);
                                    $model->save();

                                    $this->makeRelationship($model, $relationshipName);
                                    $related_identifiers[] = $model->getNode()->getId();
                                } else {
                                    $related_identifiers[] = $entry['identifier'];

                                    $model_name = 'App\Models\\' . $config['model_name'];
                                    $model = new $model_name();
                                    $model->setNode(NodeService::getById($entry['identifier']));
                                    $model->update($entry);
                                }
                            }
                        } else {
                            if (! empty($config['link_only']) && $config['link_only']) {
                                // Destroy the relationships, those are temporary
                                // Remove the link between the end node and this node
                                $model_name = 'App\Models\\' . $config['model_name'];

                                $relationships = $this->getRelationships($model_name::$NODE_TYPE);

                                foreach ($relationships as $relationship) {
                                    $relationship->delete();
                                }

                                // Fetch the node of the related model and create the relationship
                                $model = $this->searchNode($input['id'], $config['model_name']);

                                if (! empty($model)) {
                                    $this->makeRelationship($model, $relationshipName);

                                    $model->getNode()->relateTo($this->getNode(), $config['reverse_relationship'])->save();
                                }
                            } else {
                                // Check if an identifier is provided, if not, perform a create
                                if (empty($input['identifier'])) {
                                    $model_name = 'App\Models\\' . $config['model_name'];
                                    $model = new $model_name($input);
                                    $model->save();

                                    $this->makeRelationship($model, $relationshipName);
                                    $related_identifiers[] = $model->getNode()->getId();
                                } else {
                                    $related_identifiers[] = $input['identifier'];

                                    $node = NodeService::getById($input['identifier']);

                                    $model_name = 'App\Models\\' . $config['model_name'];

                                    $model = new $model_name();
                                    $model->setNode($node);
                                    $model->update($input);
                                }
                            }
                        }
                    }

                    $this->node->save();

                    if (empty($config['link_only']) || ! $config['link_only']) {
                        // Delete all of the remaining related models that had no identifiers passed (== deleted)
                        $related_nodes = $this->getRelatedNodes($relationshipName, lcfirst($config['model_name']));

                        foreach ($related_nodes as $related_node) {
                            $related_node = $related_node->current();

                            if (! in_array($related_node->getId(), $related_identifiers)) {
                                $model_name = 'App\Models\\' . $config['model_name'];
                                $model = new $model_name();
                                $model->setNode($related_node);
                                $model->delete();
                            }
                        }
                    }
                } elseif (! empty($config['link_only']) && $config['link_only']) {
                    // Remove the link between the end node and this node
                    $model_name = 'App\Models\\' . $config['model_name'];

                    $relationships = $this->getRelationships($model_name::$NODE_TYPE);

                    foreach ($relationships as $relationship) {
                        $relationship->delete();
                    }
                }
            }

            // Remove the tree of implicit nodes
            $uuid_label = $client->makeLabel($this->getGeneralId());

            $implicit_nodes = NodeService::getNodesForLabel($uuid_label);

            foreach ($implicit_nodes as $implicit_node) {
                $relationships = $implicit_node->getRelationships([]);

                foreach ($relationships as $relationship) {
                    $relationship->delete();
                }

                $implicit_node->delete();
            }

            // Create the implicit nodes
            foreach ($this->implicitModels as $config) {
                $relationship = $config['relationship'];
                $model_config = $config['config'];

                $input = @$properties[$model_config['key']];

                if (! empty($input)) {
                    // We can have multiple instances of an implicit node (e.g. multiple dimensions)
                    // Check which of the cases it is by checking whether the array is associative or not
                    if (is_array($input) && ! $this->isAssoc($input)) {
                        foreach ($input as $entry) {
                            $related_node = $this->createImplicitNode($entry, $model_config);

                            // Make the relationship
                            $this->node->relateTo($related_node, $relationship)->save();
                        }
                    } else {
                        $related_node = $this->createImplicitNode($input, $model_config);

                        // Make the relationship
                        $this->node->relateTo($related_node, $relationship)->save();
                    }
                }
            }
        }

        $this->node->save();

        return $this->node;
    }

    /**
     * Set the properties of the model
     *
     * @param array $properties The full list of properties for the model
     *
     * @return void
     */
    protected function setProperties($properties)
    {
        // Set value properties for the node
        foreach ($this->properties as $propertyConfig) {
            $propertyName = $propertyConfig['name'];

            if (! empty($properties[$propertyName])) {
                $this->node->setProperty($propertyName, $properties[$propertyName]);
            } elseif (array_key_exists('default_value', $propertyConfig)) {
                $this->node->setProperty($propertyName, $propertyConfig['default_value']);
            } else {
                $this->node->setProperty($propertyName, '');
            }
        }

        $this->node->save();
    }

    /**
     * Add timestamps to a node in ISO8601 format
     * if they already exist, only update the
     * updated_at timestamp
     *
     * @return void
     */
    protected function addTimestamps()
    {
        $timestamp = Carbon::now();

        $this->node->setProperty('updated_at', $timestamp->toIso8601String())->save();

        if (empty($this->node->getProperty('created_at'))) {
            $this->node->setProperty('created_at', $timestamp->toIso8601String())->save();
        }
    }

    /**
     * Add a unique ID to the node
     *
     * @return void
     */
    protected function addUniqueId()
    {
        // Add a unique id to the node
        $idName = lcfirst(static::$NODE_NAME) . 'Id';
        $idNode = $idNode = $this->createValueNode('identifier', ['E42', $idName, $this->getGeneralId()], $this->node->getId());

        $this->node->relateTo($idNode, 'P1')->save();
    }

    protected function createImplicitNode($input, $config)
    {
        // If the variable value_node is set, this means a simple creation of a node is
        // viable and can be automated. If not the specific create function will be called
        // to create the further internal model. Basically this means we only need to do
        // a one to one translation from a value and a node -> make the node and set the value property.
        if (! empty($config['value_node']) && $config['value_node']) {
            $related_node = $this->createValueNode($config['key'], [$config['cidoc_type']], $input);
        } else {
            $create_function = 'create' . ucfirst($config['name']);
            $related_node = $this->$create_function($input);
        }

        return $related_node;
    }

    public function getGeneralId()
    {
        $result = $this->node->getProperty($this->uniqueIdentifier);

        if (is_array($result)) {
            return array_shift($result);
        }

        return $result;
    }

    public function save()
    {
        $this->node->save();

        $cidoc_label = self::makeLabel(static::$NODE_TYPE);
        $human_label = self::makeLabel(static::$NODE_NAME);
        $medea_label = self::makeLabel('MEDEA_NODE');

        $this->node->addLabels([$cidoc_label, $human_label, $medea_label]);

        if ($this->hasUniqueId) {
            $this->addUniqueId();
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
        foreach ($this->relatedModels as $relationshipName => $config) {
            if ($config['cascade_delete']) {
                $relationships = $this->node->getRelationships([$relationshipName], Relationship::DirectionOut);

                $model_name = 'App\Models\\' . $config['model_name'];

                foreach ($relationships as $relationship) {
                    $end_node = $relationship->getEndNode();

                    $model = new $model_name();
                    $model->setNode($end_node);
                    try {
                        $model->delete();
                    } catch (Exception $ex) {
                        // Do nothing, this can happen
                        // when a node is already deleted, but is referenced by others
                    }
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
    public function createValueNode($name, $labels, $value)
    {
        $client = self::getClient();

        $generalId = $this->getGeneralId();

        $node = NodeService::makeNode();

        $node_labels = [self::makeLabel($generalId)];

        foreach ($labels as $label) {
            $node_labels[] = self::makeLabel($label);
        }

        $node->addLabels($node_labels);
        $node->setProperty('value', $value);
        $node->setProperty('name', $name);
        $node->save();

        return $node;
    }

    public function getId()
    {
        return $this->node->getId();
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

        // Get the values of the related models
        foreach ($this->relatedModels as $relationship => $config) {
            $model_name = 'App\Models\\' . ucfirst($config['model_name']);

            $related_nodes = $this->getRelatedNodes(
                $relationship,
                $model_name::$NODE_TYPE
            );

            foreach ($related_nodes as $related_node) {
                $end_node = $related_node->current();

                $related_model = new $model_name();
                $related_model->setNode($end_node);
                $related_model_values = $related_model->getValues();

                $relationship_key = $config['key'];

                if (! empty($related_model_values)) {
                    if (! empty($config['plural']) && $config['plural']) {
                        if (! empty($config['nested']) && $config['nested']) {
                            $key = key($related_model_values);
                            $values = $related_model_values[$key];

                            if (empty($data[$relationship_key])) {
                                $data[$relationship_key][$key] = [];
                            }

                            $data[$relationship_key][$key][] = $values;
                        } else {
                            if (empty($data[$relationship_key])) {
                                $data[$relationship_key] = [];
                            }

                            $data[$relationship_key][] = $related_model_values;
                        }
                    } else {
                        $data[$relationship_key] = $related_model_values;
                    }
                }
            }
        }

        // Add the computed property identifier to the implicit models in order to fetch it with the values
        $this->implicitModels[] = [
            'relationship' => 'P1',
            'config' => [
                'key' => 'identifier',
                'name' => 'identifier',
                'plural' => false,
                'cidoc_type' => 'E42'
            ]
        ];

        // For every implicit model, fetch its related nodes according to the configuration of that model
        foreach ($this->implicitModels as $model_config) {
            // Fetch the related node(s)
            $related_nodes = $this->getImplicitRelatedNodes(
                $model_config['relationship'],
                $model_config['config']['cidoc_type'],
                $model_config['config']['name']
            );

            // For every related node, check if it's a value node or not
            // Then add the value of the node (and subtree of the node if it's not a value node)
            // according to the configuration (e.g. is it plural/nested)
            foreach ($related_nodes as $related_node) {
                $related_node = $related_node->current();
                $node_name = $model_config['config']['name'];

                // Parse value nodes
                if (! empty($related_node->getProperty('value'))) {
                    if (! empty($model_config['config']['plural']) && $model_config['config']['plural']) {
                        if (empty($data[$node_name])) {
                            $data[$node_name] = [];
                        }

                        $data[$node_name][] = $related_node->getProperty('value');
                    } else {
                        $data[$node_name] = $related_node->getProperty('value');
                    }
                } else {
                    $values = $this->getImplicitValues($related_node);

                    // Parse non-value nodes
                    // Check for duplicate relationships (= build an array of values)
                    if (! empty($values)) {
                        if (! empty($model_config['config']['plural']) && $model_config['config']['plural']) {
                            if (! empty($model_config['config']['nested']) && $model_config['config']['nested']) {
                                $key = key($values);
                                $values = $values[$key];

                                if (empty($data[$node_name][$key])) {
                                    $data[$node_name][$key] = [];
                                }

                                $data[$node_name][$key][] = $values;
                            } else {
                                if (empty($data[$node_name])) {
                                    $data[$node_name] = [];
                                }

                                $data[$node_name][] = $values;
                            }
                        } else {
                            if (! empty($model_config['config']['nested']) && $model_config['config']['nested']) {
                                $key = key($values);
                                $values = $values[$key];

                                $data[$node_name][$key] = $values;
                            } else {
                                $data[$node_name] = $values;
                            }
                        }
                    }
                }
            }
        }

        // Get the data properties
        $nodeProperties = array_merge(
            $this->properties,
            [
                ['name' => 'created_at'],
                ['name' => 'updated_at']
            ]
        );

        foreach ($nodeProperties as $property) {
            $val = $this->node->getProperty($property['name']);

            if (! is_null($val)) {
                $data[$property['name']] = $val;
            }
        }

        // Add the identifier
        $data['identifier'] = $this->node->getId();

        return $data;
    }

    /**
     * Search a node and retrieve the Node object
     * this is necessary for example when the base node doesn't need
     * to instantiate new nodes, but rather needs to link with existing ones
     *
     * @param integer $nodeId
     * @param string $model
     *
     * @return null|Model
     * @throws Exception
     */
    protected function searchNode($nodeId, $model)
    {
        $node = NodeService::getById($nodeId);

        if (empty($node)) {
            return;
        }

        foreach ($node->getLabels() as $label) {
            // We can use the model name as a label because
            // the models that we fetch are all related models
            // meaning they have CIDOC labels and model name labels
            if (mb_strtolower($label->getName()) == mb_strtolower($model)) {
                 $model_name = 'App\Models\\' . $model;
                 $model = new $model_name();
                 $model->setNode($node);

                 return $model;
            }
        }

        return null;
    }

    protected function getImplicitValues($implicit_node)
    {
        $data = [];

        foreach ($implicit_node->getRelationships([], Relationship::DirectionOut) as $relationship) {
            $end_node = $relationship->getEndNode();

            if (! empty($end_node->getProperty('value'))) {
                $data[$end_node->getProperty('name')] = $end_node->getProperty('value');
            } else {
                if (! empty($data[$end_node->getProperty('name')])) {
                    $tmp = $data[$end_node->getProperty('name')];
                    $data[$end_node->getProperty('name')] = [$tmp];
                    $data[$end_node->getProperty('name')][] = $this->getImplicitValues($end_node);
                } elseif (! empty($end_node->getProperty('name'))) {
                    $data[$end_node->getProperty('name')] = $this->getImplicitValues($end_node);
                }
            }
        }

        return $data;
    }

    /**
     * Retrieve a node's related nodes through the relationship type and the end node type
     *
     * @param string $rel_type
     * @param string $endnode_type
     *
     * @return array
     */
    protected function getImplicitRelatedNodes($rel_type, $endnode_type, $node_name)
    {
        $node_id = $this->node->getId();

        $tenantStatement = NodeService::getTenantWhereStatement(['n', 'end']);

        $query = 'MATCH (n:' . static::$NODE_TYPE . ")-[$rel_type]->(end:$endnode_type)
                  WHERE id(n) = $node_id AND end.name = '$node_name' AND $tenantStatement
                  RETURN distinct end";

        $cypher_query = new Query($this->getClient(), $query);

        return $cypher_query->getResultSet();
    }

    /**
     * Retrieve a node's related nodes through the relationship type and the end node type
     *
     * @param string $rel_type
     * @param string $endnode_type
     *
     * @return ResultSet
     */
    protected function getRelatedNodes($rel_type, $endnode_type)
    {
        $node_id = $this->node->getId();

        $tenantStatement = NodeService::getTenantWhereStatement(['n', 'end']);

        $query = 'MATCH (n:' . static::$NODE_TYPE . ")-[$rel_type]->(end:$endnode_type)
                  WHERE id(n) = $node_id AND $tenantStatement
                  RETURN distinct end";

        $cypher_query = new Query($this->getClient(), $query);

        return $cypher_query->getResultSet();
    }

    /**
     * Retrieve the relationships between a specified node
     * and the current node
     *
     * @param  string $nodeType     The type of the node
     * @param  string $relationship The type of the relationship
     * @return array
     */
    protected function getRelationships($nodeType)
    {
        $nodeId = $this->node->getId();

        $tenantStatement = NodeService::getTenantWhereStatement(['n', 'end']);

        $query = 'MATCH (n:' . static::$NODE_TYPE . ")-[r]-(end:$nodeType)
                  WHERE id(n) = $nodeId AND $tenantStatement
                  RETURN r";

        $cypher_query = new Query($this->getClient(), $query);

        $results = $cypher_query->getResultSet();

        $relationships = [];

        foreach ($results as $result) {
            $relationships[] = $result['r'];
        }

        return $relationships;
    }

    protected function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Create a UUID MEDEA id
     *
     * @return string
     */
    protected function createMedeaId()
    {
        return 'MEDEA' . sha1(str_random(10) . '__' . time());
    }
}

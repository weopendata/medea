<?php

namespace App\Repositories;

use App\Models\Context;
use App\NodeConstants;
use App\Services\NodeService;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;

class BaseRepository
{
    /**
     * BaseRepository constructor.
     *
     * @param $label
     * @param $model
     */
    public function __construct($label, $model)
    {
        $this->label = $label;
        $this->model = $model;
    }

    /**
     * @param array $properties
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function store(array $properties)
    {
        $instance = new $this->model($properties);
        $instance->save();

        return $instance->getId();
    }

    /**
     * @param integer $nodeId
     * @param array $nodeData
     * @return bool
     * @throws \Everyman\Neo4j\Exception
     */
    public function update($nodeId, $nodeData)
    {
        $node = $this->getById($nodeId);

        if (empty($node)) {
            \Log::warning("No node found with contextId $nodeId");

            return false;
        }

        $instance = new $this->model();
        $instance->setNode($node);

        $instance->update($nodeData);

        return true;
    }

    /**
     * @param integer $id
     * @return array|\Everyman\Neo4j\Node|null
     * @throws \Everyman\Neo4j\Exception
     */
    public function getById($id)
    {
        $client = $this->getClient();

        $node = $client->getNode($id);

        if (empty($node)) {
            return;
        }

        foreach ($node->getLabels() as $label) {
            if ($label->getName() == $this->label && $node->getProperty(NodeConstants::TENANT_LABEL) == env('DB_TENANCY_LABEL')) {
                return $node;
            }
        }

        return null;
    }

    /**
     * @param $internalId
     * @return array|\Everyman\Neo4j\Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function getByInternalId($internalId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['n']);

        $queryString = "
        MATCH (n:$this->label)
        WHERE n.internalId = {internalId} AND $tenantStatement
        RETURN n
        LIMIT 1";

        $variables = [
            'internalId' => $internalId
        ];

        $cypherQuery = new Query($this->getClient(), $queryString, $variables);

        foreach ($cypherQuery->getResultSet() as $row) {
            $neo4jId = $row['n']->getId();

            return $this->getById($neo4jId);
        }
    }

    /**
     * @param string $property
     * @param string $value
     * @return array|\Everyman\Neo4j\Node
     * @throws \Everyman\Neo4j\Exception
     */
    public function getByProperty(string $property, $value)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['n']);

        $queryString = "
        MATCH (n:$this->label)
        WHERE n." . $property . " = {propertyValue} AND $tenantStatement
        RETURN n
        LIMIT 1";

        $variables = [
            'propertyValue' => $value
        ];

        $cypherQuery = new Query($this->getClient(), $queryString, $variables);

        foreach ($cypherQuery->getResultSet() as $row) {
            $neo4jId = $row['n']->getId();

            return $this->getById($neo4jId);
        }
    }

    /**
     * Get all the bare nodes
     *
     * @param integer $limit
     * @param integer $offset
     *
     * @return \Everyman\Neo4j\Query\Row
     * @throws \Everyman\Neo4j\Exception
     * @throws \Exception
     */
    public function getAll()
    {
        $client = $this->getClient();

        $label = $client->makeLabel($this->label);

        return NodeService::getNodesForLabel($label);
    }

    /**
     * Return the configured
     *
     * @return \Everyman\Neo4j\Label
     */
    protected function getLabel()
    {
        $client = $this->getClient();

        // Return a label configured client, equivalent of only returning a certain eloquent model
        return $client->makeLabel($this->label);
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create a new client with user and password
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        return $client;
    }

    /**
     * @param integer $id
     * @return bool
     * @throws \Everyman\Neo4j\Exception
     */
    public function delete($id)
    {
        $node = $this->getById($id);

        if (empty($node)) {
            return false;
        }

        // Check if the node type (=label) is valid
        $valid = false;

        foreach ($node->getLabels() as $label) {
            if ($label->getName() == $this->label && $node->getProperty(NodeConstants::TENANT_LABEL) == env('DB_TENANCY_LABEL')) {
                $valid = true;
                break;
            }
        }

        if ($valid) {
            // Invoke the delete method on the wrapper model
            $model = new $this->model();
            $model->setNode($node);
            $model->delete();
        }

        return $valid;
    }

    /**
     * Fetches the relevant data that the front-end needs
     * in order to visualize a certain find and it's related data
     *
     * @param integer
     *
     * @return array
     * @throws \Everyman\Neo4j\Exception
     */
    public function expandValues($id)
    {
        $node = $this->getById($id);

        if (empty($node)) {
            return [];
        }

        $model = new $this->model();
        $model->setNode($node);

        return $model->getValues();
    }
}

<?php

namespace App\Repositories;

use App\NodeConstants;
use Everyman\Neo4j\Client;

class BaseRepository
{
    /**
     * BaseRepository constructor.
     * @param $label
     * @param $model
     */
    public function __construct($label, $model)
    {
        $this->label = $label;
        $this->model = $model;
    }

    public function getById($id)
    {
        $client = $this->getClient();

        $node = $client->getNode($id);

        if (empty($node)) {
            return [];
        }

        foreach ($node->getLabels() as $label) {
            if ($label->getName() == $this->label && $node->getProperty(NodeConstants::TENANT_LABEL) == env('DB_TENANCY_LABEL')) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Return the configured
     *
     * @return \Everyman\Neo4j\Label
     */
    protected function getLabel()
    {
        $client = $this->getclient();

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

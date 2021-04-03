<?php

namespace App\Repositories;

use Everyman\Neo4j\Client;

class BaseRepository
{
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
            if ($label->getName() == $this->label) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Return the configured
     *
     * @return Label
     */
    protected function getLabel()
    {
        $client = $this->getClient();

        // Return a label configured client, equivalent of only returning a certain eloquent model
        $label = $client->makeLabel($this->label);

        return $label;
    }

    protected function getClient()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create a new client with user and password
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        return $client;
    }

    public function delete($id)
    {
        $client = $this->getClient();

        $node = $client->getNode($id);

        if (! empty($node)) {
            // Check if the node is valid, namely does it have the correct label!
            // IDs = universal, labels = types
            $valid = false;

            foreach ($node->getLabels() as $label) {
                if ($label->getName() == $this->label) {
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

        return false;
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

        if (! empty($node)) {
            $model = new $this->model();
            $model->setNode($node);

            return $model->getValues();
        }

        return [];
    }
}

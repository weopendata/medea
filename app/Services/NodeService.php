<?php


namespace App\Services;


use App\NodeConstants;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Label;

/**
 * Class NodeService
 * @package App\Services
 */
class NodeService
{
    private static $client;

    /**
     * @param $id
     * @return \Everyman\Neo4j\Node|void
     * @throws \Everyman\Neo4j\Exception
     */
    public static function getById($id)
    {
        $client = self::getClient();

        $node = $client->getNode($id);

        if (empty($node)) {
            return;
        }

        if ($node->getProperty(NodeConstants::TENANT_LABEL) == env('DB_TENANCY_LABEL')) {
            return $node;
        }
    }

    /**
     * @return Client
     */
    protected static function getClient()
    {
        if (!isset(self::$client)) {
            $neo4j_config = \Config::get('database.connections.neo4j');

            // Create a new client with user and password
            $client = new Client($neo4j_config['host'], $neo4j_config['port']);
            $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

            self::$client = $client;
        }

        return self::$client;
    }

    /**
     * @param string |array $propertyName
     * @return string | null
     * @throws \Exception
     */
    public static function getTenantWhereStatement($propertyNames)
    {
        if (empty($propertyNames)) {
            return;
        }

        $tenantLabel = self::getTenantLabelValue();

        if (is_string($propertyNames)) {
            $propertyNames = [$propertyNames];
        }

        $statements = [];

        foreach ($propertyNames as $propertyName) {
            $statements[] = $propertyName . '.' . NodeConstants::TENANT_LABEL . "='" . $tenantLabel . "'";
        }

        return implode(' AND ', $statements);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function getTenantLabelValue()
    {
        $tenantLabel = env('DB_TENANCY_LABEL');

        if (empty($tenantLabel)) {
            throw new \Exception("Configure the DB_TENANCY_LABEL in order to read/write data.");
        }

        return $tenantLabel;
    }

    /**
     * @param Label $label
     * @param array $properties
     * @return \Everyman\Neo4j\Query\Row
     * @throws \Exception
     */
    public static function getNodesForLabel(Label $label, $properties = [])
    {
        $properties[NodeConstants::TENANT_LABEL] = self::getTenantLabelValue();

        return $label->getNodes($properties);
    }

    /**
     * @return \Everyman\Neo4j\Node
     * @throws \Everyman\Neo4j\Exception
     */
    public static function makeNode()
    {
        $client = self::getClient();

        $node = $client->makeNode();
        $node->setProperty(NodeConstants::TENANT_LABEL, self::getTenantLabelValue())->save();

        return $node;
    }
}

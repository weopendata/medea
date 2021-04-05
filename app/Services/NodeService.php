<?php


namespace App\Services;


use App\NodeConstants;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Label;

class NodeService
{
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

<?php


namespace App\Services;


use App\NodeConstants;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Label;
use Everyman\Neo4j\Query\Row;

/**
 * Class NodeService
 *
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
     * @param  string | array $propertyNames
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
     * @param  Label      $label
     * @param  array|null $properties
     * @param  int|null   $limit
     * @param  int|null   $offset
     * @return \Everyman\Neo4j\Query\Row
     * @throws \Exception
     */
    public static function getNodesForLabel(Label $label, ?array $properties = [], ?int $limit = 50, ?int $offset = 0): Row
    {
        $whereStatement = self::getTenantWhereStatement(['n']);

        foreach ($properties as $key => $value) {
            $whereStatement .= " AND n.$key=\"$value\"";
        }

        $query = "MATCH (n:{$label->getName()}) WHERE $whereStatement RETURN n";

        if (!empty($limit) || !empty($offset)) {
            $query .= ' order by n.id';
        }

        if (!empty($offset)) {
            $query .= " skip $offset";
        }

        if (!empty($limit)) {
            $query .= " limit $limit";
        }

        $cypherQuery = new Query(self::getClient(), $query);
        $results = $cypherQuery->getResultSet();

        if ($results->count() < 1) {
            return new Row(self::getClient(), [], []);
        }

        // Push everything into 1 row, as that is what the Jadell getNodesForLabel does
        $nodes = [];

        foreach ($results as $result) {
            $nodes[] = $result[0];
        }

        return new Row(self::getClient(), [], $nodes);
    }

    /**
     * @param  Label    $label
     * @return int
     */
    public static function getNodesCountForLabel(Label $label)
    {
        $whereStatement = self::getTenantWhereStatement(['n']);

        $query = "MATCH (n:{$label->getName()}) WHERE $whereStatement RETURN count(n) as count";

        $cypherQuery = new Query(self::getClient(), $query);
        $results = $cypherQuery->getResultSet();

        if ($results->count() < 1) {
            return 0;
        }

        return $results[0]['count'];
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

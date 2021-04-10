<?php

namespace App\Console\Commands;

use App\NodeConstants;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Illuminate\Console\Command;

class AddTenantLabel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medea:add-tenant-label';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command adds a tenant label for all nodes that have no tenant label yet.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Make sure there's a tenant label configured
        $appTenantLabel = env('DB_TENANCY_LABEL');

        if (empty($appTenantLabel)) {
            $this->info("There is not app tenant label set.");

            return;
        }

        // For every node that has no tenant label, add a label, do it in batch
        $maxId = $this->getMaxId();

        if (empty($maxId)) {
            $this->info("No max Id found, ending script.");
        }

        $startCount = $this->getNodeCount($appTenantLabel);

        $this->info("There are $startCount nodes with the multi-tenant label.");

        $offset = 0;

        while ($offset < $maxId) {
            $this->info("Adding the multi-tenant label to a chunk of nodes...");

            $this->addMultiTenantLabel($appTenantLabel, $offset);

            $offset += 1000;
        }

        $endCount = $this->getNodeCount($appTenantLabel);

        $this->info("We ended the script with $endCount nodes having the multi-tenant label.");
    }

    /**
     * @param $appTenantLabel
     * @param $offset
     * @return void
     */
    private function addMultiTenantLabel($appTenantLabel, $offset)
    {
        $tenantLabel = NodeConstants::TENANT_LABEL;

        $maxId = $offset + 1000;

        $whereStatement = "WHERE n.$tenantLabel IS NULL AND id(n) >= $offset AND id(n) <= $maxId";

        $query = "MATCH (n) $whereStatement SET n.$tenantLabel = '$appTenantLabel'";

        $cypher_query = new Query($this->getClient(), $query);
        $cypher_query->getResultSet();
    }

    /**
     * @param string $appTenantLabel
     * @return int
     */
    private function getNodeCount($appTenantLabel)
    {
        $tenantLabel = NodeConstants::TENANT_LABEL;

        $query = "MATCH (n) WHERE n.$tenantLabel='$appTenantLabel' return count(n)";

        $cypher_query = new Query($this->getClient(), $query);
        $result = $cypher_query->getResultSet();

        return $this->getResult($result);
    }

    /**
     * NOTE: We're assuming we need to add a tenant label to nodes that don't have one, so there's no check on the tenant label itself
     *
     * @return
     */
    private function getMaxId()
    {
        $tenantLabel = NodeConstants::TENANT_LABEL;

        $whereStatement = "WHERE n.$tenantLabel IS NULL";

        $query = "MATCH (n) $whereStatement RETURN max(id(n))";

        $client = $this->getClient();

        $cypher_query = new Query($client, $query);
        $result = $cypher_query->getResultSet();

        return $this->getResult($result);
    }

    /**
     * @param $result
     * @return mixed
     */
    private function getResult($result)
    {
        if ($result->count() > 0) {
            return $result->current()->current();
        }

        return null;
    }

    private function getClient()
    {
        $neo4jConfig = \Config::get('database.connections.neo4j');

        // Create an admin
        $client = new Client($neo4jConfig['host'], $neo4jConfig['port']);
        $client->getTransport()->setAuth($neo4jConfig['username'], $neo4jConfig['password']);

        return $client;
    }
}

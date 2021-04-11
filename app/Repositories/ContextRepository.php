<?php


namespace App\Repositories;


use App\Models\Context;
use App\Services\NodeService;
use Everyman\Neo4j\Cypher\Query;

class ContextRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('S22', Context::class);
    }

    /**
     * @param array $properties
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function store(array $properties)
    {
        $context = new Context($properties);
        $context->save();

        return $context->getId();
    }

    /**
     * @param integer $contextId
     * @param array $contextInfo
     * @return bool
     * @throws \Everyman\Neo4j\Exception
     */
    public function update($contextId, $contextInfo)
    {
        $contextNode = $this->getById($contextId);

        if (empty($contextNode)) {
            \Log::warning("No node found with contextId $contextId");

            return false;
        }

        $context = new Context();
        $context->setNode($contextNode);

        $context->update($contextInfo);

        return true;
    }

    /**
     * Retrieve a Context object by its own C0, C1, ... ID
     *
     * @param string $contextId
     * @return \Everyman\Neo4j\Node|null
     * @throws \Everyman\Neo4j\Exception
     */
    public function getByContextId($contextId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['context', 'contextIdValue']);

        $queryString = "
        MATCH (context:S22)-[P149]->(contextId:E15)-[P37]->(contextIdValue:E42)
        WHERE contextIdValue.value = {contextId} AND $tenantStatement
        RETURN context
        LIMIT 1";

        $variables = [
            'contextId' => $contextId
        ];

        $cypherQuery = new Query($this->getClient(), $queryString, $variables);

        foreach ($cypherQuery->getResultSet() as $row) {
            $neo4jId = $row['context']->getId();

            return $this->getById($neo4jId);
        }
    }
}

<?php


namespace App\Repositories;


use App\Models\Context;
use App\Services\NodeService;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Exception;

class ContextRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('S22', Context::class);
    }

    /**
     * @param  int $contextId
     * @return string
     * @throws Exception
     */
    public function getRelatedContextInternalId(int $contextId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['context']);

        $queryString = "MATCH (context:S22)-[:O22]->(relatedContext:S22) 
        WHERE id(context)={contextId} AND $tenantStatement
        RETURN relatedContext.internalId as internalId";

        $variables = [
            'contextId' => $contextId,
        ];

        $cypherQuery = new Query($this->getClient(), $queryString, $variables);

        // Return the first hit
        foreach ($cypherQuery->getResultSet() as $row) {
            return $row['internalId'];
        }
    }
}

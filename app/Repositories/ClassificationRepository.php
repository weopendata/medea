<?php

namespace App\Repositories;

use App\Models\ProductionClassification;
use App\Services\NodeService;
use Everyman\Neo4j\Cypher\Query;
use PhpParser\Node;

/**
 * Class ClassificationRepository
 * 
 * @package App\Repositories
 */
class ClassificationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(ProductionClassification::$NODE_TYPE, ProductionClassification::class);
    }

    /**
     * @param $classification_id
     * @param $user_id
     * @return array|\Everyman\Neo4j\Node|\Everyman\Neo4j\Path|\Everyman\Neo4j\Query\Row|\Everyman\Neo4j\Relationship|mixed|null |null
     * @throws \Exception
     */
    public function getVoteOfUser($classification_id, $user_id)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['person', 'n']);

        $query = "MATCH (person:person)-[r:agree|disagree]-(n:productionClassification) WHERE id(n) = $classification_id AND id(person) = $user_id AND $tenantStatement RETURN r";

        $client = $this->getClient();

        $cypher_query = new Query($client, $query);
        $result = $cypher_query->getResultSet();

        if ($result->count() > 0) {
            return $result->current()->current();
        }

        return null;
    }

    /**
     * Return the user of a classification
     *
     * @param integer $classificationId
     * @return Node
     * @throws \Exception
     */
    public function getUser($classificationId)
    {
        $tenantStatement = NodeService::getTenantWhereStatement(['person', 'n']);

        $query = "MATCH (person:person)<-[r:addedBy]-(n:productionClassification) where id(n)=$classificationId AND $tenantStatement return person";

        $client = $this->getClient();

        $cypher_query = new Query($client, $query);
        $result = $cypher_query->getResultSet();

        if ($result->count() > 0) {
            return $result->current()->current();
        }

        return null;
    }

    /**
     * Link a classification to a user
     *
     * @param Node $classification
     * @param Node $person
     * @return void
     */
    public function linkClassificationToUser($classification, $person)
    {
        $classification->relateTo($person, 'addedBy')->save();
    }

    /**
     * Link publications to a classification
     *
     * @param Node $classification
     * @param array $publications An array of IDs of publications
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function linkPublications($classification, $publications)
    {
        $publicationsRepo = app()->make('App\Repositories\PublicationRepository');

        foreach ($publications as $publicationId) {
            $publication = $publicationsRepo->getById($publicationId);

            if (!empty($publication)) {
                $classification->relateTo($publication, 'P108')->save();
                $publication->relateTo($classification, 'P67')->save();
            }
        }
    }
}

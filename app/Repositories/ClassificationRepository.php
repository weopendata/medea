<?php

namespace App\Repositories;

use App\Models\ProductionClassification;
use Everyman\Neo4j\Cypher\Query;

class ClassificationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(ProductionClassification::$NODE_TYPE, ProductionClassification::class);
    }

    public function getVoteOfUser($classification_id, $user_id)
    {
        $query = "match (person:person)-[r]-(n:productionClassification) where id(n) = $classification_id AND id(person) = $user_id return r";

        $client = $this->getClient();

        $cypher_query = new Query($client, $query);
        $result = $cypher_query->getResultSet();

        if ($result->count() > 0) {
            return $result->current()->current();
        } else {
            return null;
        }
    }

    /**
     * Link publications to a classification
     *
     * @param  Node  $classification
     * @param  array $publications   An array of IDs of publications
     * @return array
     */
    public function linkPublications($classification, $publications)
    {
        foreach ($publications as $publicationId) {
            $publication = $this->getById($publicationId);

            if (! empty($publication)) {
                $classification->relateTo($publication, 'P108');
                $publication->relateTo($classification, 'P67');
            }
        }
    }
}

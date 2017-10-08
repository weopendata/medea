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
     * Return the user of a classification
     *
     * @param  integer $classificationId
     * @return Node
     */
    public function getUser($classificationId)
    {
        $query = "match (person:person)<-[r:addedBy]-(n:productionClassification) where id(n)=$classificationId return person";

        // TODO: refactor this into a helper function that parses first results from a response
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
     * Link a classification to a user
     *
     * @param  Node $classification
     * @param  Node $person
     * @return void
     */
    public function linkClassificationToUser($classification, $person)
    {
        $classification->relateTo($person, 'addedBy')->save();
    }

    /**
     * Link publications to a classification
     *
     * @param  Node  $classification
     * @param  array $publications   An array of IDs of publications
     * @return void
     */
    public function linkPublications($classification, $publications)
    {
        $publicationsRepo = app()->make('App\Repositories\PublicationRepository');

        foreach ($publications as $publicationId) {
            $publication = $publicationsRepo->getById($publicationId);

            if (! empty($publication)) {
                $classification->relateTo($publication, 'P108')->save();
                $publication->relateTo($classification, 'P67')->save();
            }
        }
    }
}

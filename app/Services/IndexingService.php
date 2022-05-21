<?php

namespace App\Services;

use App\Repositories\ElasticSearch\FindRepository;
use \App\Repositories\FindRepository as Neo4JFindRepository;

class IndexingService
{
    /**
     * @param  array $find
     * @return void
     * @throws \Exception
     */
    public function indexFind(array $find)
    {
        $elasticSearchId = array_get($find, 'elasticSearchId');

        if (empty($elasticSearchId)) {
            $findDocument = app(FindRepository::class)->getByNeo4jId($find['identifier']);

            if (!empty($findDocument)) {
                $elasticSearchId = $findDocument['id'];
            }
        }

        if (empty($elasticSearchId)) {
            $elasticSearchId = app(FindRepository::class)->store($find);

            if (empty($elasticSearchId)) {
                throw new \Exception("Something went wrong while indexing find with ID " . $find['identifier']);
            }

            $findNode = app(Neo4JFindRepository::class)->getByID($find['identifier']);
            $findNode->setProperty('elasticSearchId', $elasticSearchId);

            return;
        }

        app(FindRepository::class)->update($elasticSearchId, $find);
    }
}
<?php

namespace App\Services;

use App\Repositories\ElasticSearch\FindRepository;

class IndexingService
{
    /**
     * @param  array $find A flat representation of find data that can be indexed
     * @return void
     * @throws \Exception
     */
    public function indexFind(array $find)
    {
        $elasticSearchId = null;

        $findDocument = app(FindRepository::class)->getByNeo4jId($find['identifier']);

        if (!empty($findDocument)) {
            $elasticSearchId = $findDocument['id'];
        }

        if (empty($elasticSearchId)) {
            $elasticSearchId = app(FindRepository::class)->store($find);

            if (empty($elasticSearchId)) {
                throw new \Exception("Something went wrong while indexing find with ID " . $find['identifier']);
            }

            return;
        }

        app(FindRepository::class)->update($elasticSearchId, $find);
    }
}
<?php

namespace App\Repositories\ElasticSearch;

use Elastica\Client;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Search;

class BaseRepository
{
    /**
     * The Elastica client object
     *
     * @var Client
     */
    protected $client;

    /**
     * The Elasticsearch index of the repository
     *
     * @var Index
     */
    protected $index;

    /**
     * The total hits of the last performed query
     *
     * @var integer
     */
    protected $totalHits;

    /**
     * Construct a base elastic repository
     *
     * @param  string $indexName
     * @throws \Exception
     */
    public function __construct(string $indexName)
    {
        $elasticSearchConfig = config('database.connections.elasticsearch');
        $elasticSearchConfig['index'] = $indexName;

        $this->client = new Client($elasticSearchConfig);
        $this->index = $this->client->getIndex($elasticSearchConfig['index']);
    }

    /**
     * @param  string $pathToMappingFile
     * @return void
     */
    public function createIndexIfAbsent(string $pathToMappingFile)
    {
        $indexExists = $this->index->exists();

        if ($indexExists) {
            return;
        }

        $indexConfiguration = json_decode(file_get_contents($pathToMappingFile), true);

        $settings = $indexConfiguration['settings'];
        $mappingProperties = $indexConfiguration['mappings']['properties'];

        $this->index->create();
        $this->index->close();
        $this->index->setSettings($settings);
        $this->index->setMapping(new Mapping($mappingProperties));
        $this->index->open();
    }

    /**
     * @param  string $elasticSearchId
     * @return bool
     */
    public function delete(string $elasticSearchId): bool
    {
        try {
            $response = $this->index->deleteById($elasticSearchId);

            return $response->isOk();
        } catch (\Exception $ex) {
            \Log::error($ex->getMessage());
            \Log::error($ex->getTraceAsString());
        }

        return false;
    }

    /**
     * Create a Search object based on the index and type
     *
     * @return Search
     */
    protected function createSearch()
    {
        $search = new Search($this->client);
        $search->addIndex($this->index);

        return $search;
    }

    /**
     * Perform a Search and parse the necessary data from the resultSet object
     *
     * @param  Search $search
     * @param  array  $fields
     * @return array
     */
    protected function performSearch($search, $fields = [])
    {
        $resultSet = $search->search();

        $documents = [];

        foreach ($resultSet->getResults() as $result) {
            $data = $result->getData();

            if (!empty($fields)) {
                $data = array_only($data, $fields);
            }

            $data['id'] = $result->getId();

            $documents[] = $data;
        }

        $this->totalHits = $resultSet->getTotalHits();

        return $documents;
    }

    /**
     * Return the total hits of the last executed query
     *
     * @return integer
     */
    public function getTotalHits()
    {
        return $this->totalHits;
    }

    /**
     * Return the name of the index
     *
     * @return string|null
     */
    public function getIndex()
    {
        if (empty($this->client)) {
            return;
        }

        return $this->index->getName();
    }

    /**
     * @param  array $aggregationBuckets
     * @return array
     */
    protected function transformAggregationBucket(array $aggregationBuckets)
    {
        $results = [];

        foreach ($aggregationBuckets as $result) {
            $results[$result['key']] = @$result['doc_count'] ?? 0;
        }

        return $results;
    }

    /**
     * @return int
     */
    public function getIndexCount()
    {
        return $this->index->count();
    }

    /**
     * @return void
     */
    public function refreshIndex()
    {
        $this->index->refresh();
    }
}
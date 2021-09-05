<?php


namespace App\Repositories;


use App\Models\ExcavationEvent;
use App\Models\SearchArea;
use App\Services\NodeService;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Exception;

class ExcavationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(ExcavationEvent::$NODE_TYPE, ExcavationEvent::class);
    }

    /**
     * @param array $properties
     * @return int
     * @throws \Everyman\Neo4j\Exception
     */
    public function store(array $properties)
    {
        $excavation = new ExcavationEvent($properties);

        $excavation->save();

        return $excavation->getId();
    }

    /**
     * @param string $excavationUUID
     * @return array
     * @throws \Exception
     */
    public function getMetaDataForExcavation($excavationUUID)
    {
        $tentantStatement = NodeService::getTenantWhereStatement(['excavationEvent', 'searchArea']);

        $queryString = "MATCH (excavationEvent:A9)-[r:AP3]->(searchArea:E27), (excavationEvent:A9)-[:P1]->(excavationTitle:E41) WHERE  $tentantStatement AND excavationEvent.internalId = {excavationUUID}  RETURN excavationEvent, excavationTitle, searchArea";

        $variables = [
            'excavationUUID' => $excavationUUID
        ];

        $cypherQuery = new Query($this->getClient(), $queryString, $variables);

        $metaData = [];

        // Return the first hit
        foreach ($cypherQuery->getResultSet() as $row) {
            $excavation = $row['excavationEvent'];
            $searchAreaNode = $row['searchArea'];

            $searchArea = new SearchArea();
            $searchArea->setNode($searchAreaNode);
            $searchArea = $searchArea->getValues();

            $metaData['searchArea'] = $searchArea;
            $metaData['internalId'] = $excavation->getProperty('internalId');
            $metaData['excavationTitle'] = $row['excavationTitle']->getProperty('value');

            break;
        }

        return $metaData;
    }
}

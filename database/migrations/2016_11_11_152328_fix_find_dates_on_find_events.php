<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Carbon\Carbon;

class FixFindDatesOnFindEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $neo4j_config = \Config::get('database.connections.neo4j');

        // Create a new client with user and password
        $client = new Client($neo4j_config['host'], $neo4j_config['port']);
        $client->getTransport()->setAuth($neo4j_config['username'], $neo4j_config['password']);

        $message = "MATCH (n:E52) return n";

        $query = new Query($client, $message);
        $results = $query->getResultSet();

        foreach ($results as $result) {
            $findDateNode = $result['findDate'];

            $timestamp = $this->fixDate($findDateNode->value);

            $findDateNode->value = $timestamp;
            $findDateNode->save();
        }
    }

    private function fixDate($findDate)
    {
        try {
            $date = new Carbon($findDate);

            return $date->toDateString();
        } catch (\Exception $ex) {
            if ($findDate == 'onbekend') {
                return Carbon::now()->toDateString();
            } elseif (strrpos($findDate, '-') !== false) {
                $parts = explode('-', $findDate);

                if ($parts[0] > 1000 && count($parts) == 3) {
                    $date = new Carbon($parts[0] . '-' . $parts[1] . '-' . $parts[2]);

                    return $date->toDateString();
                } elseif ($parts[0] < 1000 && count($parts) == 3) {
                    $date = new Carbon($parts[2] . '-' . $parts[1] . '-' . $parts[0]);

                    return $date->toDateString();
                } else {
                    $date = new Carbon($parts[0]);

                    return $date->toDateString();
                }
            } elseif (strrpos($findDate, '/') !== false) {
                $parts = explode('/', $findDate);

                if ($parts[0] > 1000 && count($parts) == 3) {
                    $date = new Carbon($parts[0] . '-' . $parts[1] . '-' . $parts[2]);

                    return $date->toDateString();
                } elseif ($parts[0] < 1000 && count($parts) == 3) {
                    $date = new Carbon($parts[2] . '-' . $parts[1] . '-' . $parts[0]);

                    return $date->toDateString();
                } else {
                    $date = new Carbon($parts[0]);

                    return $date->toDateString();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

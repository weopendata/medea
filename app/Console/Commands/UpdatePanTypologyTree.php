<?php

namespace App\Console\Commands;

use App\Repositories\Eloquent\PanTypologyRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class UpdatePanTypologyTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medea:update-pan-typology';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the PAN typology based tree structure.';

    /**
     * @const string
     */
    const PAN_TOP_LEVEL_URI = 'https://portable-antiquities.nl/pan/services/Rest/poolparty/topconcepts?language=nl';

    /**
     * This URI will return small collections consisting of a minimum data, so no specific properties, but mainly links towards children
     *
     * @const string
     */
    const PAN_DATA_BASE_URI = 'https://portable-antiquities.nl/pan/services/Rest/poolparty/childconceptswithimage/';

    /**
     * This URI will return all properties available for a specific type
     *
     * @const string
     */
    const PAN_DETAIL_BASE_URI = 'https://portable-antiquities.nl/pan/services/Rest/poolparty/properties/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function handle()
    {
        $this->info("Building up the top level typologies...");

        // Start by fetching the top level concepts, then for every top level concept, recursively build the branches of the tree
        $topLevelTypologies = $this->fetchTopLevelTypologies();

        foreach ($topLevelTypologies as $topLevelTypology) {
            $this->upsertTypology($topLevelTypology);
        }

        $this->info("Building the branches based on the top level typologies...");

        $topLevelCodes = array_pluck($topLevelTypologies, 'code');

        foreach ($topLevelCodes as $topLevelCode) {
            $this->buildBranch($topLevelCode);
        }
    }

    /**
     * @param $code
     * @throws GuzzleException
     */
    private function buildBranch(string $code)
    {
        $uri = self::PAN_DATA_BASE_URI . $code . '?language=nl';

        $this->info("Building branch from URI " . $uri);

        $typologies = $this->makePanRequest($uri);

        if (empty($typologies)) {
            $this->info("WARNING: skipping branch for $uri, an empty set was returned by PAN or it was a leaf node.");

            return;
        }

        foreach ($typologies as $typology) {
            $this->upsertTypology($typology);
        }

        $branchCodes = array_pluck($typologies, 'code');

        if (empty($branchCodes)) {
            return;
        }

        foreach ($branchCodes as $branchCode) {
            $this->buildBranch($branchCode);
        }
    }

    /**
     * @param array $typology
     * @return void
     */
    private function upsertTypology(array $typology)
    {
        // Fetch the details of the typology
        $detailUri = self::PAN_DETAIL_BASE_URI . $typology['code'];

        try {
            $typology = $this->makePanRequest($detailUri);
        } catch (GuzzleException $ex) {
            $this->error("ERROR: for $detailUri we didn't get any data back. Error: " . $ex->getMessage());

            return;
        }

        // Make sure we got a proper set of data back
        if (empty($typology)) {
            $this->error("ERROR: for $detailUri we didn't get any data back.");

            return;
        }

        // Transform the raw typology data into a something that our Typology model can handle
        $typology = [
          'code' => trim($typology['code']),
          'label' => trim($typology['prefLabel'] ?? ''),
          'uri' => trim($typology['uri']),
          'meta' => $typology
        ];

        try {
            app(PanTypologyRepository::class)->updateOrCreate($typology);
        } catch (\Exception $ex) {
            $this->error("Something went wrong while upserting a typology: " . $ex->getMessage());
            $this->error($ex->getTraceAsString());
        }
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    private function fetchTopLevelTypologies()
    {
        return $this->makePanRequest(self::PAN_TOP_LEVEL_URI);
    }

    /**
     * @param string $uri
     * @return mixed
     * @throws GuzzleException
     */
    private function makePanRequest(string $uri)
    {
        $client = new Client([
            'base_uri' => $uri,
            'timeout' => 60
        ]);

        try {
            $response = $client->request('GET');

            $data = $response->getBody()->getContents();

            return json_decode($data, true);
        } catch (\Exception $ex) {
            $this->error("Something went wrong while fetching PAN data: " . $ex->getMessage());
            $this->error($ex->getTraceAsString());

            // It might be because we're doing too many requests
            sleep(10);
        }
    }
}

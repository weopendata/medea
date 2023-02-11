<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Context;
use App\Models\ExcavationEvent;
use App\Models\FindEvent;
use App\Models\Person;
use App\Repositories\CollectionRepository;
use App\Repositories\ContextRepository;
use App\Repositories\ElasticSearch\FindRepository as ElasticFindRepository;
use App\Repositories\ExcavationRepository;
use App\Repositories\FindRepository;
use App\Repositories\UserRepository;
use App\Services\IndexingService;
use App\Services\NodeService;
use Illuminate\Console\Command;

class DataManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medea:management';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage MEDEA data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FindRepository $finds, UserRepository $users)
    {
        parent::__construct();

        $this->finds = $finds;
        $this->users = $users;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Warning! Any changes you commit through this command immediately affects the data that in your Neo4j data store.');

        $this->info('Choose from the following commands: (enter the number)');
        $this->info('1. Remove all finds.');
        $this->info('2. Remove all users.');
        $this->info('3. Remove single find.');
        $this->info('4. Remove all excavations.');
        $this->info('5. Remove single excavation.');
        $this->info('6. Remove all contexts.');
        $this->info('7. Remove all collections.');
        $this->info('8. Index all finds');

        $choice = $this->ask('Enter your choice of action');

        $this->executeCommand($choice);
    }

    private function executeCommand($choice)
    {
        switch ($choice) {
            case 1:
                if ($this->confirm("Are you sure you want to remove all finds?")) {
                    $this->removeAllFinds();
                }
                break;
            case 2:
                if ($this->confirm("Are you sure you want to remove all users?")) {
                    $this->removeAllUsers();
                }
                break;
            case 3:
                $this->removeSingleFind();
                break;
            case 4:
                $repository = app(ExcavationRepository::class);
                $class = ExcavationEvent::class;

                if ($this->confirm("Are you sure you want to remove all excavations?")) {
                    $this->removeAll($repository, $class);
                }

                break;
            case 5:
                $this->removeSingleExcavation();
                break;
            case 6:
                $repository = app(ContextRepository::class);
                $class = Context::class;

                if ($this->confirm("Are you sure you want to remove all contexts?")) {
                    $this->removeAll($repository, $class);
                }

                break;
            case 7:
                $repository = app(CollectionRepository::class);
                $class = Collection::class;

                if ($this->confirm("Are you sure you want to remove all collections?")) {
                    $this->removeAll($repository, $class);
                }

                break;
            case 8:
                $this->indexFinds();
                break;
            default:
                break;
        }
    }

    /**
     * @return void
     * @throws \Everyman\Neo4j\Exception
     */
    private function removeSingleFind()
    {
        $id = $this->ask('Enter the ID of the find we can remove');

        $this->finds->delete($id);

        $this->info('Removed find with ID ' . $id);
    }

    /**
     * @return void
     */
    private function removeSingleExcavation()
    {
        $internalId = $this->ask('Enter the "internal ID" of the excavation we need to remove. This is the Id of the excavation that comes with the excavation upload.');

        $excavation = app(ExcavationRepository::class)->getByInternalId($internalId);
        app(ExcavationRepository::class)->delete($excavation->id);
    }

    /**
     * @return void
     * @throws \Everyman\Neo4j\Exception
     */
    private function removeAllUsers()
    {
        if ($this->isAppRunningInProduction()) {
            $this->info("You cannot do this in a production environment.");
        }

        $count = 0;

        $userNodes = $this->users->getAllNodes();

        $bar = $this->output->createProgressBar(count($userNodes));

        foreach ($userNodes as $userNode) {
            $person = new Person();
            $person->setNode($userNode);

            $person->delete();

            $bar->advance();
            $count++;
        }

        $this->info('');
        $this->info("Removed $count Person nodes.");
    }

    /**
     * @return void
     * @throws \Everyman\Neo4j\Exception
     */
    private function removeAllFinds()
    {
        if ($this->isAppRunningInProduction()) {
            $this->info("You cannot do this in a production environment.");
        }

        $count = 0;

        $findNodes = $this->finds->getAllNodes();

        $bar = $this->output->createProgressBar(count($findNodes));

        foreach ($findNodes as $findNode) {
            $find = new FindEvent();
            $find->setNode($findNode);

            $find->delete();

            $bar->advance();
            $count++;
        }

        $this->info('');
        $this->info("Removed $count FindEvent nodes.");
    }

    /**
     * @param         $repository
     * @param  string $class
     * @return void
     * @throws \Everyman\Neo4j\Exception
     */
    private function removeAll($repository, string $class)
    {
        if ($this->isAppRunningInProduction()) {
            $this->info("You cannot do this in a production environment.");
        }

        $count = 0;

        $nodes = $repository->getAllNodes();

        $bar = $this->output->createProgressBar(count($nodes));

        foreach ($nodes as $node) {
            if (is_array($node) && !empty($node['identifier'])) {
                $node = NodeService::getById($node['identifier']);
            }

            $model = new $class();
            $model->setNode($node);

            $model->delete();

            $bar->advance();
            $count++;
        }

        $this->info('');
        $this->info("Removed $count nodes.");
    }

    /**
     * @return void
     * @throws \Everyman\Neo4j\Exception
     */
    private function indexFinds()
    {
        app(\App\Repositories\ElasticSearch\FindRepository::class)->createIndexIfAbsent(database_path('mappings/elastic_search_finds_mapping.json'));

        $findsCount = app(FindRepository::class)->getCountOfAllFinds();
        $bar = $this->output->createProgressBar($findsCount);

        $limit = 20;
        $offset = 0;

        $finds = app(FindRepository::class)->getAllWithFilter([], $limit, $offset);

        while (count($finds['data']) > 0) {
            foreach ($finds['data'] as $find) {
                try {
                    app(IndexingService::class)->indexFind($find['identifier']);

                    // Factor in a short resting period
                    // The reason why is that Neo4j or the system that runs it runs out of open files
                    // and needs time to close them again. Not adding a sleep causes a curl error 7 - could not connect
                    // indicating that the Neo4J can't handle anymore requests, which is due to the maximum of open files.
                    sleep(1);
                } catch (\Exception $ex) {
                    \Log::error("Something went wrong while indexing find " . $find['identifier']);
                    \Log::error($ex->getMessage());
                    \Log::error($ex->getTraceAsString());
                }

                $bar->advance();
            }

            $offset += $limit;

            $finds = app(FindRepository::class)->getAllWithFilter([], $limit, $offset);
        }

        $elasticCount = app(ElasticFindRepository::class)->getIndexCount();

        $this->info('');
        $this->info("We found $findsCount FindEvent nodes, the index contains $elasticCount documents");
    }

    /**
     * @return bool
     */
    private function isAppRunningInProduction(): bool
    {
        return strtolower(env('APP_ENV')) === 'production';
    }
}

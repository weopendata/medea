<?php

namespace App\Console\Commands;

use App\Models\Context;
use App\Models\ExcavationEvent;
use App\Repositories\ContextRepository;
use App\Repositories\ExcavationRepository;
use Illuminate\Console\Command;
use App\Repositories\FindRepository;
use App\Repositories\UserRepository;
use App\Models\FindEvent;
use App\Models\Person;

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
        $this->info('5. Remove all contexts.');

        $choice = $this->ask('Enter your choice of action: ');
        $this->executeCommand($choice);
    }

    private function executeCommand($choice)
    {
        switch ($choice) {
            case 1:
                $this->removeAllFinds();
                break;
            case 2:
                $this->removeAllUsers();
                break;
            case 3:
                $this->removeSingleFind();
                break;
            case 4:
                $repository = app(ExcavationRepository::class);
                $class = ExcavationEvent::class;

                $this->removeAll($repository, $class);
                break;
            case 5:
                $repository = app(ContextRepository::class);
                $class = Context::class;

                $this->removeAll($repository, $class);
                break;
                break;
            default:
                break;
        }
    }

    private function removeSingleFind()
    {
        $id = $this->ask('Enter the ID of the find we can remove: ');

        $this->finds->delete($id);

        $this->info('Removed find with ID ' . $id);
    }

    private function removeAllUsers()
    {
        $count = 0;

        $userNodes = $this->users->getAll();

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

    private function removeAllFinds()
    {
        $count = 0;

        $findNodes = $this->finds->getAll();

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

    private function removeAll($repository, $class)
    {
        $count = 0;

        $nodes = $repository->getAll();

        $bar = $this->output->createProgressBar(count($nodes));

        foreach ($nodes as $node) {
            $model = new $class();
            $model->setNode($node);

            $model->delete();

            $bar->advance();
            $count++;
        }

        $this->info('');
        $this->info("Removed $count nodes.");
    }
}

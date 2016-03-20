<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\FindRepository;
use App\Models\FindEvent;

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
    public function __construct(FindRepository $finds)
    {
        parent::__construct();

        $this->finds = $finds;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Warning! Any changes you commit through this command immediatly effect the data that is withing the Neo4j of the MEDEA project.");

        $this->info("Choose from the following commands: (enter the number)");
        $this->info("1. Remove all finds.");

        $choice = $this->ask("Enter your choice of action: ");
        $this->executeCommand($choice);
    }

    private function executeCommand($choice)
    {
        switch ($choice) {
            case 1:
                $this->removeAllFinds();
                break;
            default:
                break;
        }
    }

    private function removeAllFinds()
    {
        $count = 0;

        foreach ($this->finds->getAll(0, 500) as $find_node) {
            $find = new FindEvent();
            $find->setNode($find_node);

            $find->delete();
            $count++;
        }

        $this->info("Removed $count FindEvent nodes.");
    }
}

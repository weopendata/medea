<?php

namespace App\Console\Commands;

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
    public function __construct()
    {
        parent::__construct();
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

        $choice = $this->ask("Enter your choice of action: ");
        $this->executeCommand($choice);
    }

    private function executeCommand($choice)
    {
        switch ($choice) {
            case 1:
                break;
            default:
                break;
        }
    }
}

<?php

namespace App\Console;

use App\Console\Commands\ImportFinds;
use App\Console\Commands\DataManagement;
use App\Console\Commands\SendNotificationsForSavedSearches;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DataManagement::class,
        ImportFinds::class,
        SendNotificationsForSavedSearches::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('medea:notify-saved-searches')->dailyAt('23:59');
    }
}

<?php

namespace App\Providers;

use App\Events\FindEventStored;
use App\Events\FindEventUpdated;
use App\Listeners\HandleFindEventStored;
use App\Listeners\HandleFindEventUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        FindEventStored::class => [
            HandleFindEventStored::class
        ],

        FindEventUpdated::class => [
            HandleFindEventUpdated::class
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}

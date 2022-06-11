<?php

namespace App\Listeners;

use App\Events\FindEventStored;
use App\Services\IndexingService;

class HandleFindEventStored
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  FindEventStored  $event
     * @return void
     */
    public function handle(FindEventStored $event)
    {
        app(IndexingService::class)->indexFind($event->getFindId());
    }
}

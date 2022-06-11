<?php

namespace App\Listeners;

use App\Events\FindEventUpdated;
use App\Services\IndexingService;

class HandleFindEventUpdated
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
     * @param  FindEventUpdated  $event
     * @return void
     */
    public function handle(FindEventUpdated $event)
    {
        app(IndexingService::class)->indexFind($event->getFindId());
    }
}

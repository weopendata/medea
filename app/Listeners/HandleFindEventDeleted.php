<?php

namespace App\Listeners;

use App\Events\FindEventDeleted;
use App\Services\IndexingService;

class HandleFindEventDeleted
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
     * @param  FindEventDeleted $event
     * @return void
     */
    public function handle(FindEventDeleted $event)
    {
        app(IndexingService::class)->deleteFind($event->getFindId());
    }
}

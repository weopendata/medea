<?php

namespace App\Listeners;

use App\Events\FindEventStored;
use App\Repositories\FindRepository;
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
        // Fetch a simplified version of the find event using the get all method
        $finds = app(FindRepository::class)->getAllWithFilter(['id' => $event->getFindId()]);

        if (empty($finds) || empty($finds['data'])) {
            return;
        }

        app(IndexingService::class)->indexFind($finds['data'][0]);
    }
}

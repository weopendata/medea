<?php

namespace App\Listeners;

use App\Events\FindEventUpdated;
use App\Repositories\FindRepository;
use App\Services\IndexingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        // Fetch a simplified version of the find event using the get all method
        $finds = app(FindRepository::class)->getAllWithFilter(['id' => $event->getFindId()]);

        if (empty($finds) || empty($finds['data'])) {
            return;
        }

        app(IndexingService::class)->indexFind($finds['data'][0]);
    }
}

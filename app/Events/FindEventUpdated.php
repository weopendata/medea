<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FindEventUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    private $findId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $findId)
    {
        $this->findId = $findId;
    }

    /**
     * @return int
     */
    public function getFindId(): int
    {
        return $this->findId;
    }
}

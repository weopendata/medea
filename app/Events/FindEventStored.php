<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FindEventStored
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
    public function getFindId()
    {
        return $this->findId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FindEventDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var
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

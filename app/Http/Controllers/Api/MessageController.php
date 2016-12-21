<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\FindRepository;
use App\Http\Requests\SendMessageRequest;

class MessageController extends Controller
{
    public function __construct(FindRepository $finds)
    {
        $this->finds = $finds;
    }

    public function sendMessage(SendMessageRequest $request)
    {
        dd($request->message);
        dd('send message');
    }
}

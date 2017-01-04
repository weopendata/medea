<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\FindRepository;
use App\Http\Requests\SendMessageRequest;
use App\Repositories\UserRepository;
use App\Mailers\AppMailer;
use App\Models\Person;

class MessageController extends Controller
{
    public function __construct(FindRepository $finds)
    {
        $this->finds = $finds;
    }

    public function sendMessage(SendMessageRequest $request, UserRepository $users, AppMailer $mailer)
    {
        // Get the user
        $userNode = $users->getById($request->input('user_id'));

        $user = new Person();
        $user->setNode($userNode);

        $mailer->sendPlatformMessage($request->input('message'), $user);

        return redirect()->back()->with('message', 'Uw bericht werd verstuurd!');
    }
}

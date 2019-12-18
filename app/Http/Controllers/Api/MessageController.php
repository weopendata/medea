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

        $recipient = new Person();
        $recipient->setNode($userNode);

        try {
            $mailer->sendPlatformMessage($request->input('message'), $recipient, $request->user());

            return response()->json(['message' => 'Uw bericht werd verstuurd']);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Er is iets fout gelopen bij het versturen van uw bericht, contacteer de beheerder als dit probleem zich blijft voordoen.']);
        }
    }
}

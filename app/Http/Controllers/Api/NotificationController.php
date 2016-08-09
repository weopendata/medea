<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Helpers\Pager;
use App\Repositories\NotificationRepository;

class NotificationController extends Controller
{
    public function __construct(NotificationRepository $notifications)
    {
        $this->notifications = $notifications;
    }

    public function index(Request $request)
    {
        // Get the user id from request
        $user = $request->user();
        $userId = $user->getNode()->getId();

        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);

        // Get the status of notifications to fetch, by default
        // the status is not taken into consideration
        $status = $request->get('read');

        // Get the relevant notifications, desc based on timestamp
        $notifications = $this->notifications->getForUser($userId, $status, $limit, $offset);

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read
     *
     * @param integer $notificationId
     *
     * @return Response
     */
    public function setRead($notificationId, Request $request)
    {
        $status = $request->input('read');

        if (!is_null($status)) {
            $result = $this->notifications->setRead($notificationId, $status);

            if ($result) {
                if ((boolean) $status) {
                    return response()->json(["message" => "Bericht werd gemarkeerd als gelezen."]);
                } else {
                    return response()->json(["message" => "Bericht werd gemarkeerd als ongelezen."]);
                }
            } else {
                return response()->json(
                    [
                        "message" => "Er is iets fout gelopen bij het markeren als gelezen van het bericht."
                    ],
                    400
                );
            }
        }

        return abort(400, 'No property "read" has been found in the request body.');
    }
}

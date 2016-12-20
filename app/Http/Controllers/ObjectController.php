<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\ObjectRepository;
use App\Repositories\NotificationRepository;
use PiwikTracker;

class ObjectController extends Controller
{
    public function __construct(
        ObjectRepository $objects,
        NotificationRepository $notifications
    ) {
        $this->objects = $objects;
        $this->notifications = $notifications;
    }

    /**
     * We expect the body to hold the new validationstatus of the find
     * TODO check if the object exists
     * @param string
     *
     * @return Response
     */
    public function validation($objectId, Request $request)
    {
        $input = $request->json()->all();

        $input['timestamp'] = date('c');

        $embargo = false;

        if (! empty($input['embargo'])) {
            $embargo = $input['embargo'];
        }

        $this->objects->setValidationStatus($objectId, $input['objectValidationStatus'], $input['feedback'], $embargo);

        // Add a notification for the user
        $this->addNotification($objectId, $input);

        // Track the validation change with Piwik
        $this->registerPiwikEvent($request->user()->id, $input['objectValidationStatus']);

        return response()->json(['success' => true]);
    }

    /**
     * Add a notification about the new validation status
     *
     * @param integer $objectId The id of the object
     * @param array   $input    The input of the request
     *
     * @return void
     */
    private function addNotification($objectId, $input)
    {
        $message = 'Uw vondst werd behandeld door een validator. De nieuwe status is: ' . $input['objectValidationStatus'];

        // If the status is revision, then add a link to the edit page, if not set the link to the find URI
        $url = url('/finds/' . $this->objects->getRelatedFindEventId($objectId));

        if ($input['objectValidationStatus'] == 'Aan te passen') {
            $url .= '/edit';
        }

        $userId = $this->objects->getRelatedUserId($objectId);

        $this->notifications->store([
            'message' => $message,
            'url' => $url,
            'user_id' => $userId
        ]);
    }

    /**
     * Register a validation status update event
     *
     * @param integer $userId
     * @param string  $action
     * @return
     */
    private function registerPiwikEvent($userId, $action)
    {
        if (! empty(env('PIWIK_SITE_ID')) && ! empty(env('PIWIK_URI'))) {
            PiwikTracker::$URL = env('PIWIK_URI');
            $piwikTracker = new PiwikTracker(env('PIWIK_SITE_ID'));

            $piwikTracker->setUserId($userId);
            $piwikTracker->doTrackEvent('Validation', $action, $userId);
        }
    }
}

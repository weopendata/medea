<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\ObjectRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use App\Repositories\FindRepository;
use App\Models\Person;
use PiwikTracker;

class ObjectController extends Controller
{
    public function __construct(
        ObjectRepository $objects,
        NotificationRepository $notifications,
        UserRepository $users,
        FindRepository $finds
    ) {
        $this->objects = $objects;
        $this->notifications = $notifications;
        $this->users = $users;
        $this->finds = $finds;
    }

    /**
     * We expect the body to hold the new validationstatus of the find
     *
     * @param string
     *
     * @return Response
     */
    public function validation($objectId, Request $request)
    {
        $input = $request->json()->all();

        $input['timestamp'] = date('c');

        $embargo = 'false';

        if (! empty($input['embargo'])) {
            $embargo = (string) $input['embargo'];
        }

        // The third argument must be the entire validation input
        $this->objects->setValidationStatus($objectId, $input['objectValidationStatus'], $input, $embargo);

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
        $find = $this->finds->expandValues($this->objects->getRelatedFindEventId($objectId));

        $title = makeFindTitle($find);

        $message = "Uw vondst: $title, werd behandeld. De nieuwe status is: " . $input['objectValidationStatus'];

        // If the status is revision, then add a link to the edit page, if not set the link to the find URI
        $url = url('/finds/' . $this->objects->getRelatedFindEventId($objectId));

        if ($input['objectValidationStatus'] == 'Aan te passen') {
            $url .= '/edit';
        }

        $userId = $this->objects->getRelatedUserId($objectId);

        // It could be that the user ID does not exists, finds can be imported as well
        if (empty($userId)) {
            return;
        }

        $user = $this->users->getById($userId);

        // User not found
        if (empty($user)) {
            \Log::error('The user could not be found.');

            return;
        }

        // The person to view the profile of
        $person = new Person();
        $person->setNode($user);

        // Add a platform notification
        $this->notifications->store([
            'message' => $message,
            'url' => $url,
            'user_id' => $userId
        ]);

        // Send an email to the user
        $mailer = app()->make('App\Mailers\AppMailer');
        $mailer->sendFindStatusUpdate($person, $title, $find['identifier']);
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

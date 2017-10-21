<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\ObjectRepository;
use App\Repositories\ClassificationRepository;
use App\Repositories\NotificationRepository;
use App\Http\Requests\DeleteClassificationRequest;
use PiwikTracker;

class ClassificationController extends Controller
{
    public function __construct(
        ObjectRepository $objects,
        ClassificationRepository $classifications,
        NotificationRepository $notifications
    ) {
        $this->objects = $objects;
        $this->classifications = $classifications;
        $this->notifications = $notifications;
    }

    /**
     * Add a classification to an object
     *
     * @param $objectId             integer The id of the object
     * @param $classification array   The classification of the object
     *
     * @return Node
     */
    public function store($objectId, Request $request)
    {
        $classification = $request->json()->all();

        // Parse the publications that already exist in the platform from the classification
        // they'll need to be linked, not added as will be the case if they are passed to the
        // object repository
        list($classification, $referencedPublications) = $this->parsePublications($classification);

        $classification_node = $this->objects->addClassification($objectId, $classification);

        if (empty($classification_node)) {
            return response()->json(['errors' => ['message' => 'Something has gone wrong, make sure the object exists.']], 404);
        }

        // Link the user and the classification
        $user = auth()->user();
        $this->classifications->linkClassificationToUser($classification_node, $user->getNode());

        // Add the referenced publications to the classification node
        $this->classifications->linkPublications($classification_node, $referencedPublications);

        // Track the classification
        $this->registerPiwikEvent($request->user()->id, 'Created');

        // Add a notification
        $this->addNotification($objectId);

        return response()->json(['success' => true]);
    }

    /**
     * Parse the publications from a classification object and return them in a
     * repository compatible way as well as the links to those publications that
     * indicate the relevant pages of the linked publication
     *
     * @param  array $classification array
     * @return array
     */
    private function parsePublications($classification)
    {
        $referencedPublications = [];

        if (! empty($classification['publication'])) {
            $newPublications = [];

            foreach ($classification['publication'] as $publication) {
                if (empty($publication['identifier'])) {
                    $newPublications[] = $publication;
                } else {
                    $referencedPublications[] = $publication['identifier'];
                }
            }

            $classification['publication'] = $newPublications;
        } else {
            unset($classification['publication']);
            unset($classification['publicationPages']);
        }

        // Clean up the classification
        if (! empty($classification['productionClassificationSource'])) {
            $classification['productionClassificationSource'] = collect($classification['productionClassificationSource'])->filter()->values()->toArray();
        }

        return [
            $classification,
            $referencedPublications
        ];
    }

    /**
     * Add a like/dislike and add a link to the person
     *
     * @param $objectId                integer The id of the object
     * @param $classification_id integer The classification id
     *
     * @return Node
     */
    public function agree($objectId, $classification_id, Request $request)
    {
        $user = $request->user();

        $classification = $this->objects->getClassification($objectId, $classification_id);

        // Get the current votes of the user and adjust where necessary
        $vote_relationship = $this->classifications->getVoteOfUser($classification_id, $user->id);

        if (! empty($vote_relationship) && ! empty($classification)) {
            // Check which vote he casted, if he agreed, abort.
            // if he disagreed, remove link, adjust disagree count
            $type = $vote_relationship->getType();

            if ($type == 'agree') {
                return response()->json(['errors' => ['message' => 'The user has already agreed to this classification.']], 400);
            }

            $result = $vote_relationship->delete();

            $disagree = $classification->getProperty('disagree');

            $disagree--;

            $classification->setProperty('disagree', $disagree)->save();
        }

        if (! empty($classification)) {
            $agree = $classification->getProperty('agree');
            $agree++;

            $classification->setProperty('agree', $agree)->save();
            $user->getNode()->relateTo($classification, 'agree')->save();

            return $agree;
        }

        return [];
    }

    /**
     * Add a like/dislike and add a link to the person
     *
     * @param $objectId                integer The id of the object
     * @param $classification_id integer The classification id
     * @param $request  Request
     *
     * @return Node
     */
    public function disagree($objectId, $classification_id, Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;

        $classification = $this->objects->getClassification($objectId, $classification_id);

        // Get the current votes of the user and adjust where necessary
        $vote_relationship = $this->classifications->getVoteOfUser($classification_id, $user_id);

        if (! empty($vote_relationship) && ! empty($classification)) {
            // Check which vote he casted, if he agreed, abort.
            // if he disagreed, remove link, adjust disagree count
            $type = $vote_relationship->getType();

            if ($type == 'disagree') {
                return response()->json(['errors' => ['message' => 'The user has already disagreed to this classification.']], 400);
            }

            $vote_relationship->delete();
            $agree = $classification->getProperty('agree');

            $agree--;

            $classification->setProperty('agree', $agree)->save();
        }

        if (! empty($classification)) {
            $disagree = $classification->getProperty('disagree');
            $disagree++;

            $classification->setProperty('disagree', $disagree)->save();
            $user->getNode()->relateTo($classification, 'disagree')->save();

            return $disagree;
        }

        return [];
    }

    /**
     * Add a notification about a new classification
     *
     * @param integer $objectId The id of the object
     *
     * @return void
     */
    private function addNotification($objectId)
    {
        $message = 'Er werd een nieuwe classificatie toegevoegd aan uw vondst';

        // If the status is revision, then add a link to the edit page, if not set the link to the find URI
        $url = url('/finds/' . $this->objects->getRelatedFindEventId($objectId));

        $userId = $this->objects->getRelatedUserId($objectId);

        // Finds can be imported as well, no person is attached to it
        // if that's the case
        if (empty($userId)) {
            return;
        }

        $this->notifications->store([
            'message' => $message,
            'url' => $url,
            'user_id' => $userId
        ]);
    }

    public function destroy(DeleteClassificationRequest $request, $objectId, $classification_id)
    {
        $deleted = $this->classifications->delete($classification_id);

        if ($deleted) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'The classifcation was not deleted, probably because it did not exist.']);
        }
    }

    /**
     * Register a create/update event
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
            $piwikTracker->doTrackEvent('Classification', $action, $userId);
        }
    }
}

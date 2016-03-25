<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\ObjectRepository;
use App\Repositories\ClassificationRepository;

class ClassificationController extends Controller
{
    public function __construct(ObjectRepository $objects, ClassificationRepository $classifications)
    {
        $this->objects = $objects;
        $this->classifications = $classifications;
    }

    /**
     * Add a classification to an object
     *
     * @param $id             integer The id of the object
     * @param $classification array   The classification of the object
     *
     * return Node
     */
    public function store($id, Request $request)
    {
        $classification = $request->json()->all();

        $classification_node = $this->objects->addClassification($id, $classification);

        if (empty($classification_node)) {
            return response()->json(['errors' => ['message' => 'Something has gone wrong, make sure the object exists.']], 404);
        }
        return response()->json(['success' => true]);
    }

    /**
     * Add a like/dislike and add a link to the person
     *
     * @param $id                integer The id of the object
     * @param $classification_id integer The classification id
     *
     * @return Node
     */
    public function agree($id, $classification_id, Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;

        $classification = $this->objects->getClassification($id, $classification_id);

        // Get the current votes of the user and adjust where necessary
        $vote_relationship = $this->classifications->getVoteOfUser($classification_id, $user_id);

        if (!empty($vote_relationship) && !empty($classification)) {
            // Check which vote he casted, if he agreed, abort.
            // if he disagreed, remove link, adjust disagree count
            $type = $vote_relationship->getType();

            if ($type == 'agree') {
                return response()->json(['errors' => ['message' => 'The user has already agreed to this classification.']], 400);
            }

            $vote_relationship->delete();
            $disagree = $classification->getProperty('disagree');

            $disagree--;

            $classification->setProperty('disagree', $disagree)->save();
        }

        if (!empty($classification)) {
            $agree = $classification->getProperty('agree');
            $agree++;

            $classification->setProperty('agree', $agree)->save();
            $user->getNode()->relateTo($classification, 'agrees')->save();

            return $agree;
        }

        return [];
    }

    /**
     * Add a like/dislike and add a link to the person
     *
     * @param $id                integer The id of the object
     * @param $classification_id integer The classification id
     * @param $request  Request
     *
     * @return Node
     */
    public function disagree($id, $classification_id, Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;

        $classification = $this->objects->getClassification($id, $classification_id);

        // Get the current votes of the user and adjust where necessary
        $vote_relationship = $this->classifications->getVoteOfUser($classification_id, $user_id);

        if (!empty($vote_relationship) && !empty($classification)) {
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

        if (!empty($classification)) {
            $disagree = $classification->getProperty('disagree');
            $disagree++;

            $classification->setProperty('disagree', $disagree)->save();
            $user->getNode()->relateTo($classification, 'disagrees')->save();

            return $disagree;
        }

        return [];
    }
}

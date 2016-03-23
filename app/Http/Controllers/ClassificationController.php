<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\ObjectRepository;

class ClassificationController extends Controller
{
    public function __construct(ObjectRepository $objects)
    {
        $this->objects = $objects;
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
    public function agree($id, $classification_id)
    {
        $classification = $this->objects->getClassification($id, $classification_id);

        if (!empty($classification)) {
            $agree = $classification->getProperty('agree');
            $agree++;

            $classification->setProperty('agree', $agree)->save();

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
    public function disagree($id, $classification_id, $request)
    {

    }
}

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
}

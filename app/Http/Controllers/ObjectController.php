<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\ObjectRepository;

class ObjectController extends Controller
{
    public function __construct(ObjectRepository $objects)
    {
        $this->objects = $objects;
    }

    /**
     * We expect the body to hold the new validationstatus of the find
     *
     * @param string
     */
    public function validation($id, Request $request)
    {
        $input = $request->json()->all();

        $this->objects->setValidationStatus($id, $input['objectValidationStatus']);

        return response()->json(['success' => true]);
    }
}

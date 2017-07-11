<?php

namespace App\Http\Controllers;

use App\Repositories\CollectionRepository;
use App\Http\Requests\LinkCollectionAndUserRequest;

class CollectionUserController extends Controller
{
    public function __construct(CollectionRepository $collections)
    {
        $this->collections = $collections;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function linkUser($collectionId, $userId, LinkCollectionAndUserRequest $request)
    {
        $success = $this->collections->linkUser($collectionId, $userId);

        if ($success) {
            return response()->json(['message' => 'success']);
        }

        return response()->json(['error' => 'De gebruiker werd niet gelinkt aan de collectie. Wellicht maakt deze gebruiker al deel uit van de collectie, of de gebruiker/collectie bestaat niet meer.'], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int                       $collectionId
     * @param  int                       $userId
     * @param  Request                   $request
     * @return \Illuminate\Http\Response
     */
    public function unlinkUser($collectionId, $userId, LinkCollectionAndUserRequest $request)
    {
        $success = $this->collections->unlinkUser($collectionId, $userId);

        if ($success) {
            return response()->json(['message' => 'success']);
        }

        return response()->json(['error' => 'De gebruiker kon niet verwijderd worden van de collectie. Wellicht bestaat de gebruiker of collectie niet meer, of zijn ze niet gelinkt aan elkaar.'], 400);
    }
}

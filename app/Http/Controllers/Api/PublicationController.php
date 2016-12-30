<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\PublicationRepository;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    protected $publications;

    public function __construct(PublicationRepository $publications)
    {
        $this->publications = $publications;
    }

    /**
     * Search for publications with a certain string
     * return an array of publications with their title and id
     *
     * @param  Request                  $request
     * @return Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $searchString = $request->input('searchString');

        $searchHits = [];

        if (! empty($searchString)) {
            $searchHits = $this->publications->search($searchString);
        }

        return response()->json($searchHits);
    }
}

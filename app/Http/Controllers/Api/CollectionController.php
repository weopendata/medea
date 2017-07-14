<?php

namespace App\Http\Controllers\Api;

use App\Repositories\CollectionRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __construct(CollectionRepository $collections)
    {
        $this->collections = $collections;
    }

    public function search(Request $request)
    {
        return $this->collections->search($request->input('title', ''));
    }
}

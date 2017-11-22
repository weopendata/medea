<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SuggestionRepository;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function __construct(SuggestionRepository $suggestions)
    {
        $this->suggestions = $suggestions;
    }

    public function suggest(Request $request)
    {
        $facet = $request->input('facet', null);
        $searchString = $request->input('search', null);

        return $this->suggestions->suggest($facet, $searchString);
    }
}

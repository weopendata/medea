<?php


namespace App\Http\Controllers;


use App\Repositories\Eloquent\PanTypologyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TypologyController extends Controller
{
    public function show(Request $request)
    {
        $typologyTree = Cache::get('typologytree');

        if (empty($typologyTree)) {
            $typologyTree = app(PanTypologyRepository::class)->getTree();

            Cache::put('typologytree', $typologyTree, now()->addHours(24));
        }


        return view('pages.typology-browser', ['typologyTree' => $typologyTree]);
    }
}

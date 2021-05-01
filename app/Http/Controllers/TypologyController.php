<?php


namespace App\Http\Controllers;


use App\Repositories\Eloquent\PanTypologyRepository;
use Illuminate\Http\Request;

class TypologyController extends Controller
{
    public function show(Request $request)
    {
        $typologyTree = app(PanTypologyRepository::class)->getTree();

        //jj($typologyTree);

        return view('pages.typology-browser', ['typologyTree' => $typologyTree]);
    }
}

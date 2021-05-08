<?php


namespace App\Http\Controllers;


use App\Repositories\Eloquent\PanTypologyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TypologyController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request)
    {
        $typologyInfo = Cache::get('typologyinfo');

        $typologyTree = [];
        $typologyMap = [];

        if (empty($typologyInfo)) {
            $typologyInfo = app(PanTypologyRepository::class)->getTree();
            $typologyTree = @$typologyInfo['tree'];
            $typologyMap = @$typologyInfo['map'];

            Cache::put('typologyinfo', $typologyInfo, now()->addHours(24));
        }

        return view(
            'pages.typology-browser',
            [
                'typologyTree' => $typologyTree,
                'typologyMap' => $typologyMap
            ]
        );
    }
}

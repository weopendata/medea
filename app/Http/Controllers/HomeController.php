<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Repositories\FindRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->finds = new FindRepository();
        $stats = $this->finds->getStatistics();

        $homePageTemplateName = env('HOME_PAGE_TEMPLATE_NAME');
        $homePageTemplateName = empty($homePageTemplateName) ? 'home' : 'home-' . $homePageTemplateName;

        if (file_exists(base_path('resources/views/static/' . $homePageTemplateName . '.blade.php'))) {
            return view('static.' . $homePageTemplateName, ['stats' => $stats, 'backgroundColor' => 'blue']);
        }

        return view('static.home', ['stats' => $stats]);
    }

    public function about(Request $request)
    {
        // Redirect to kirby
        return redirect(env('CMS', 'http://medea-cms.weopendata.com'));
    }

    public function contact(Request $request)
    {
        return view('static.contact');
    }

    public function disclaimer(Request $request)
    {
        return view('static.disclaimer');
    }

    public function feedback(Request $request)
    {
        return view('static.feedback');
    }

    public function help(Request $request)
    {
        return view('static.help');
    }

    public function voorwaarden(Request $request)
    {
        return view('static.voorwaarden');
    }
}

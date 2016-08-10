<?php

namespace App\Http\Controllers;

use App\Http\Requests;
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('static.home');
    }
    
    public function about(Request $request)
    {
        return view('static.about');
    }
    
    public function contact(Request $request)
    {
        return view('static.contact');
    }
    
    public function disclaimer(Request $request)
    {
        return view('static.disclaimer');
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

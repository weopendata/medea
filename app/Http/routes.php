<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('wireframes.home');
});

Route::get('/finds', function () {
    return view('wireframes.finds');
});

Route::get('/finds/create', function () {
    return view('wireframes.add-find');
});

Route::get('/register', function () {
    return view('wireframes.register');
});

Route::get('/validate', function () {
    return view('wireframes.validatelist');
});

Route::get('/validate/{id}', function () {
    return view('wireframes.validatedetail');
})->where('id', '[0-9]+');

Route::get('/classify', function () {
    return view('wireframes.classify');
});

Route::get('/classify/{id}', function () {
    return view('wireframes.classifydetail');
})->where('id', '[0-9]+');

Route::get('/api', function () {
    $api = json_decode('[
    {
      "title" : "Gouden Romeinse munt",
      "category" : "munt",
      "description" : "Een gouden munt uit de vroege romeinse tijd.",
      "dimension" : "5x5 cm",
      "date" : "20 n. Chr. - 100 n. Chr.",
      "location" : [50.806905897875, 3.3014484298127],
      "updated_at" : 1454112000
    },
    {
      "title" : "Centurion gesp",
      "category" : "gesp",
      "description" : "Een riem die aan een centurion toebehoorde.",
      "dimension" : "100x5 cm",
      "date" : "0 n. Chr. - 50 n. Chr.",
      "location" : [50.806905897875, 3.2227863148763],
      "updated_at" : 1454112000
    },
    {
      "title" : "Speer uit de Griekse periode",
      "category" : "speer",
      "description" : "Een typisch griekse speer uit de oudheid.",
      "dimension" : "200x5x8 cm",
      "date" : "200 v. Chr. - 150 n. Chr.",
      "location" : [50.806905897875, 3.2474169937074],
      "updated_at" : 1454112000
    }]
');

    return response()->json($api);
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});

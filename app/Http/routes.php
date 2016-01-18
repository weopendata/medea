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

Route::get('/finds/new', function () {
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

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

Route::group(['middleware' => 'web'], function () {
    Route::auth();
    Route::get('register/confirm/{token}', 'Auth\AuthController@confirmEmail');

    Route::get('/', 'HomeController@index');
    Route::resource('finds', 'FindController');
    Route::resource('objects/{id}/classifications', 'ClassificationController');
    Route::resource('objects/{id}/classifications/{classification_id}/agree', 'ClassificationController@agree');
    Route::resource('objects/{id}/classifications/{classification_id}/disagree', 'ClassificationController@disagree');

    Route::delete('users/{id}', 'UserController@delete');
    Route::get('settings', 'UserController@showSettings');
});

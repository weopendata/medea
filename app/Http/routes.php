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

    Route::get('/', 'HomeController@index');
    Route::get('about', 'HomeController@about');
    Route::get('contact', 'HomeController@contact');
    Route::get('disclaimer', 'HomeController@disclaimer');
    Route::get('help', 'HomeController@help');

    Route::get('voorwaarden', 'HomeController@voorwaarden');

    Route::resource('finds', 'FindController');

    Route::group(['middleware' => 'findapi'], function () {
        Route::resource('api/finds', 'Api\FindController');
    });

    Route::group(['middleware' => 'auth'], function () {
        Route::resource('users', 'UserController');
        Route::get('api/notifications', 'Api\NotificationController@index');
        Route::post('api/notifications/{id}', 'Api\NotificationController@setRead');

        Route::group(['middleware' => 'roles:validator|detectorist'], function () {
            Route::post('objects/{id}/validation', 'ObjectController@validation');
        });

        Route::group(['middleware' => 'roles:administrator'], function () {
            Route::get('register/confirm/{token}', 'Auth\RegistrationController@confirmRegistration');
            Route::get('register/deny/{token}', 'Auth\RegistrationController@denyRegistration');
        });

        Route::group(['middleware' => 'roles:detectorist|registrator|vondstexpert'], function () {
            Route::resource('objects/{id}/classifications', 'ClassificationController');
            Route::resource('objects/{id}/classifications/{classification_id}/agree', 'ClassificationController@agree');
            Route::resource('objects/{id}/classifications/{classification_id}/disagree', 'ClassificationController@disagree');
        });

        // Route::delete('users/{id}', 'UserController@delete');
        Route::get('settings', 'UserController@showSettings');
    });
});

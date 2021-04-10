<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'HomeController@index');
Route::get('about', 'HomeController@about');
Route::get('contact', 'HomeController@contact');
Route::get('feedback', 'HomeController@feedback');
Route::get('disclaimer', 'HomeController@disclaimer');
Route::get('help', 'HomeController@help');

Route::get('voorwaarden', 'HomeController@voorwaarden');

Route::resource('finds', 'FindController');
//Route::resource('persons', 'UserController');
Route::resource('collections', 'CollectionController');

Route::group(['middleware' => 'roles:validator|detectorist'], function () {
    Route::post('objects/{id}/validation', 'ObjectController@validation');
});

Route::group(['middleware' => 'roles:administrator'], function () {
    Route::get('register/confirm/{token}', 'Auth\RegistrationController@confirmRegistration');
    Route::get('register/deny/{token}', 'Auth\RegistrationController@denyRegistration');
    Route::resource('/uploads', 'UploadController');
    Route::get('/api/uploads', 'UploadController@get');
    Route::post('/api/uploads/{uploadId}/upload', 'UploadController@startUpload');
    Route::get('/api/uploads/{import_job_id}/logs', 'UploadController@getLogs');
});

Route::group(['middleware' => 'roles:detectorist|registrator|vondstexpert'], function () {
    Route::resource('objects/{id}/classifications', 'ClassificationController');
    Route::post('objects/{id}/classifications/{classification_id}/agree', 'ClassificationController@agree');
    Route::post('objects/{id}/classifications/{classification_id}/disagree', 'ClassificationController@disagree');
    Route::delete('objects/{id}/classifications/{classification_id}/agree', 'ClassificationController@deleteVote');
    Route::delete('objects/{id}/classifications/{classification_id}/disagree', 'ClassificationController@deleteVote');
});

Route::resource('api/finds', 'Api\FindController');
Route::get('api/collections', 'Api\CollectionController@search');

Route::group(['middleware' => 'auth'], function () {
    Route::resource('persons', 'UserController');

    Route::get('api/statistics', 'Api\StatisticsController@index');
    Route::get('api/notifications', 'Api\NotificationController@index');
    Route::post('api/notifications/{id}', 'Api\NotificationController@setRead');

    Route::get('api/publications', 'Api\PublicationController@search');
    Route::get('api/publications/{id}', 'Api\PublicationController@getById');
    Route::get('api/suggestions', 'Api\SuggestionController@suggest');

    Route::get('api/users', 'Api\UserController@index');

    Route::group(['middleware' => 'roles:administrator'], function () {
        Route::get('api/export', 'Api\ExportController@export');
    });

    Route::post('sendMessage', 'Api\MessageController@sendMessage');
});

Route::put('collections/{collection_id}/persons/{user_id}', 'CollectionUserController@linkUser');
Route::delete('collections/{collection_id}/persons/{user_id}', 'CollectionUserController@unlinkUser');

Route::get('settings', 'UserController@mySettings');
Route::get('settings/{id}', 'UserController@userSettings');

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');

<?php

/*Route::resource('api/finds', 'Api\FindController');
Route::get('api/collections', 'Api\CollectionController@search');

Route::group(['middleware' => 'auth'], function () {
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
});*/
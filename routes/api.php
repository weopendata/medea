<?php

Route::get('external/finds', 'Api\ThirdPartyApiController@getFinds');
Route::get('external/excavations', 'Api\ThirdPartyApiController@getExcavations');
Route::get('external/contexts', 'Api\ThirdPartyApiController@getContexts');

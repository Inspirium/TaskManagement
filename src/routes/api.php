<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Inspirium\TaskManagement\Controllers\Api', 'middleware' => ['api', 'auth:api'], 'prefix' => 'api'], function() {

	Route::group(['prefix' => 'tasks'], function() {
		Route::any('/', 'TaskController@getAllUserTasks');
	});

	Route::group(['prefix' => 'task'], function() {
		Route::get('{id}', 'TaskController@getTask');
		Route::post('/', 'TaskController@postTask');
		Route::put('{id}', 'TaskController@postTask');
	});
});

<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Inspirium\TaskManagement\Controllers\Api', 'middleware' => ['api', 'auth:api'], 'prefix' => 'api'], function() {

	Route::group(['prefix' => 'tasks'], function() {
		Route::any('/', 'TaskController@getAllUserTasks');
		Route::post('updateOrder', 'TaskController@updateOrder');
		Route::get('department/{id}', 'TaskController@getDepartmentTasks');
	});

	Route::group(['prefix' => 'task'], function() {
		Route::get('{id}', 'TaskController@getTask');
		Route::post('/', 'TaskController@postTask');
		Route::put('{id}', 'TaskController@postTask');
		Route::post('{id}/accept', 'TaskController@acceptTask');
		Route::post('{id}/reject', 'TaskController@rejectTask');
	});
});

<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Inspirium\TaskManagement\Controllers\Api', 'middleware' => ['api', 'auth:api'], 'prefix' => 'api'], function() {

	Route::group(['prefix' => 'tasks'], function() {
		Route::any('/', 'TaskController@getAllUserTasks');
		Route::post('updateOrder', 'TaskController@updateOrder');
		Route::post('requestOrder', 'TaskController@requestOrder');
		Route::get('department/{id}', 'TaskController@getDepartmentTasks');
	});

	Route::group(['prefix' => 'task'], function() {
		Route::get('{id}', 'TaskController@getTask');
		Route::delete('{id}', 'TaskController@deleteTask');
		Route::post('/', 'TaskController@postTask');
		Route::put('{id}', 'TaskController@postTask');
		Route::post('{id}/accept', 'TaskController@acceptTask');
		Route::post('{id}/reject', 'TaskController@rejectTask');
		Route::post('{id}/reassign', 'TaskController@reassignTask');
		Route::post('{id}/complete', 'TaskController@completeTask');
		Route::post('{id}/file', 'TaskController@fileSave');


		Route::post('{id}/clock/{action}', 'TaskController@clock');
	});
});

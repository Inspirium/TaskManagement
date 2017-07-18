<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Inspirium\TaskManagement\Controllers', 'middleware' => ['web', 'auth']], function() {

	Route::group(['prefix' => 'tasks'], function() {
		Route::any('/', function() {
			return view(config('app.template') . '::router-view');
		});
	});

	Route::group(['prefix' => 'task'], function() {
		Route::get('edit/{id?}', 'TaskController@editTask');

		Route::get('show/{id}', 'TaskController@showTask');
	});
});

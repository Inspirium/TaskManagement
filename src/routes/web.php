<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Inspirium\TaskManagement\Controllers', 'middleware' => ['web', 'auth'], 'prefix' => 'tasks'], function() {
    Route::get('/', function() {
        return view(config('app.template' ) . '::tasks.user_tasks');
    });
});

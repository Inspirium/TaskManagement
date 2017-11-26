<?php

namespace Inspirium\TaskManagement;

use Illuminate\Support\ServiceProvider;
use Inspirium\TaskManagement\Observers\TaskObserver;
use Inspirium\TaskManagement\Models\Task;

class TaskManagementServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        Task::observe(TaskObserver::class);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

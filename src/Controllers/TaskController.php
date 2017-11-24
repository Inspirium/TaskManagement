<?php

namespace Inspirium\TaskManagement\Controllers;

use Inspirium\Http\Controllers\Controller;
use Inspirium\TaskManagement\Models\Task;

class TaskController extends Controller {

	public function showTask($id) {
		return view(config('app.template') . '::tasks.task_details');
	}

	public function editTask($id = null) {
		return view(config('app.template') . '::tasks.task_new');
	}

	public function showTasks() {
		$tasks = Task::all();//TODO only user tasks
		return view(config('app.template' ) . '::tasks.task', ['tasks' => $tasks]);
	}
}
<?php

namespace Inspirium\TaskManagement\Controllers;

use App\Http\Controllers\Controller;
use Inspirium\TaskManagement\Models\Task;

class TaskController extends Controller {

	public function showTask($id) {
		$task = Task::firstOrFail($id);
		return view(config('app.template') . '::tasks.task_details', ['task' => $task]);
	}

	public function editTask($id = null) {
		$task = Task::firstOrNew($id);
		return view(config('app.template') . '::tasks.task_new', ['task' => $task]);
	}

	public function showTasks() {
		$tasks = Task::all();//TODO only user tasks
		return view(config('app.template' ) . '::tasks.task', ['tasks' => $tasks]);
	}
}
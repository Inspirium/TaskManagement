<?php

namespace Inspirium\TaskManagement\Observers;

use App\Notifications\TaskAssigned;
use Inspirium\TaskManagement\Models\Task;

class TaskObserver {

	public function assigned(Task $task) {
		foreach ($task->employees as $employee) {
			$user = $employee->user;
			$user->notify(new TaskAssigned($task));
		}
	}
}
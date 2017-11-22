<?php

namespace Inspirium\TaskManagement\Observers;

use Inspirium\Messaging\Models\Thread;
use Inspirium\TaskManagement\Notifications\TaskAssigned;
use Inspirium\TaskManagement\Models\Task;
use Inspirium\TaskManagement\Notifications\TaskCompleted;

class TaskObserver {

	public function assigned(Task $task) {
		foreach ($task->thread->users as $employee) {
			$user = $employee->user;
			$user->notify(new TaskAssigned($task));
		}
	}

	public function completed(Task $task) {
		$task->assigner->user->notify(new TaskCompleted($task));
	}
}
<?php

namespace Inspirium\TaskManagement\Observers;

use Inspirium\Messaging\Models\Thread;
use Inspirium\TaskManagement\Notifications\TaskAssigned;
use Inspirium\TaskManagement\Models\Task;

class TaskObserver {

	public function assigned(Task $task) {
		if (!$task->thread) {
			$task->thread()->create(['title' => $task->title]);
		}
		$task->thread->users()->sync($task->employees->pluck('id')->all());

		foreach ($task->employees as $employee) {
			$user = $employee->user;
			$user->notify(new TaskAssigned($task));
		}
	}

	public function completed(Task $task) {
		$task->assigner->user->notify(new TaskCompleted($task));
	}
}
<?php

namespace Inspirium\TaskManagement\Observers;

use Inspirium\Messaging\Models\Thread;
use Inspirium\TaskManagement\Notifications\TaskAssigned;
use Inspirium\TaskManagement\Models\Task;

class TaskObserver {

	public function assigned(Task $task) {
		if (!$task->thread) {
			$t = Thread::create(['title' => $task->title]);
			$t->users()->sync($task->employees->pluck('id')->all());
			$task->thread()->save($t);
		}
		else {
			$task->thread->users()->sync($task->employees->pluck('id')->all());
		}

		foreach ($task->employees as $employee) {
			$user = $employee->user;
			$user->notify(new TaskAssigned($task));
		}
	}

	public function completed(Task $task) {
		$task->assigner->user->notify(new TaskCompleted($task));
	}
}
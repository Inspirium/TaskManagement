<?php

namespace Inspirium\TaskManagement\Controllers\Api;

use Inspirium\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inspirium\Models\HumanResources\Department;
use Inspirium\Models\HumanResources\Employee;
use Inspirium\TaskManagement\Models\Task;
use Inspirium\TaskManagement\Notifications\TaskAssigned;

class TaskController extends Controller {

	public function getAllUserTasks() {
		$employee = Auth::id();
		$new_tasks = Task::with(['assigner', 'assignee'])->where('assignee_id', $employee)
		                                                 ->where('status', 'new')->get();

		$accepted_tasks = Task::with(['assigner', 'assignee'])->where('assignee_id', $employee)
		                                                      ->where('status', 'accepted')->get();
		$sent_tasks = Task::with(['assigner', 'assignee'])->where('assigner_id', $employee)->get();
		$rejected_tasks = Task::with(['assigner', 'assignee'])->where('assignee_id', $employee)
		                                                      ->where('status', 'rejected')->get();
		$completed_tasks = Task::with(['assigner', 'assignee'])->where('assignee_id', $employee)
		                       ->where('status', 'completed')->get();
		return response()->json(['new_tasks' => $new_tasks, 'accepted_tasks' => $accepted_tasks, 'sent_tasks' => $sent_tasks, 'rejected_tasks' => $rejected_tasks, 'completed_tasks' => $completed_tasks]);
	}

	public function getTask($id) {
		$task = Task::with(['related', 'thread', 'assigner', 'assignee'])->find($id);
		return response()->json(['task' => $task]);
	}

	public function postTask(Request $request, $id = null) {
		/** @var Task $task */
		if ($id) {
			$task = Task::find($id);
		}
		else {
			$task = new Task();
		}
		$task->name = $request->input('name');

		$task->type = $request->input('type');
		$task->description = $request->input('description');
		$task->priority = $request->input('priority');
		$deadline = Carbon::createFromFormat('d. m. Y.', $request->input('deadline'));
		$task->deadline = $deadline->toDateTimeString();
		$task->status = 'new';
		$task->assigner()->associate(Auth::user());
		$assignee = Employee::find($request->input('users')[0]['id']);
		$task->assignee()->associate($assignee);
		$task->department_id = $assignee->department_id;
		$task->save();
		$task->assignNewThread();
		return response()->json([]);
	}

	public function deleteTask($id) {
		Task::destroy($id);
		return response()->json([]);
	}

	public function reassignTask(Request $request, $id) {
		$task = Task::find($id);
		$assignee = Employee::find($request->input('employees')[0]['id']);
		$task->thread->users()->syncWithoutDetaching($assignee->id);
		$task->assignee()->associate($assignee);
		$task->save();
		$assignee->notify(new TaskAssigned($task));
		$task->load(['thread', 'assignee', 'assigner', 'related']);
		return response()->json($task);
	}

	public function acceptTask(Request $request, $id){
		$task = Task::find($id);
		$task->status = 'accepted';
		$task->save();
		if ($task->type==3) {
			$task->related->approveRequest();
			$task->status = 'completed';
			$task->save();
		}
		if ($task->type == 5) {
			$proposition = $task->related;
			$proposition->status = 'approved';
			$proposition->approved = true;
			$proposition->approved_by = Auth::id();
			$proposition->approved_on = Carbon::now();
			$proposition->save();
			$task->status = 'completed';
			$task->save();
		}
		return response()->json([]);
	}

	public function rejectTask(Request $request, $id) {
		$task = Task::find($id);
		$task->status = 'rejected';
		$task->status_info = $request->input('reason');
		$task->save();
		if ($task->type==3) {
			$task->related->rejectRequest();
		}
		if ($task->type == 5) {
			$proposition = $task->related;
			$proposition->status = 'rejected';
			$proposition->approved = false;
			$proposition->save();
			$task->status = 'completed';
			$task->save();
		}
		return response()->json([]);
	}

	public function updateOrder(Request $request) {
		$employee = Auth::user();
		$order = $request->input('tasks');
		foreach ($order as $o => $i) {
			$employee->tasks()->updateExistingPivot($i, ['order' => $o]);
		}
	}

	public function getDepartmentTasks($id) {
		$department = Department::find($id);
		$employees = $department->employees()->with(['tasks' => function($query) {
			$query->with(['assigner', 'assignee']);
		}])->get();
		return response()->json(['department' => $department, 'employees' => $employees]);
	}

	public function completeTask(Request $request, $id) {
		$task = Task::find($id);
		$task->status = 'completed';
		$task->save();
		$task->triggerCompleted();
	}

	public function clock(Request $request, $id, $action) {
		$task = Task::find($id);
		if ('start' === $action) {
			$task->is_running = true;
			$task->running_from = Carbon::now();
			$task->save();
		}
		else {
			$task->is_running = false;
			$task->running_elapsed += Carbon::now()->diffInSeconds($task->running_from);
			$task->save();
		}
		return response()->json([]);
	}

	public function fileSave(Request $request, $id) {
		$task = Task::find($id);
		$final = false;
		if ($request->input('isFinal')) {
			$final = true;
		}
		if ($task->type == 4) {
			$task->related->documents()->attach($request->input( 'file.id' ), [ 'is_final' => $final, 'type' => $task->related_link ]);
		}
		else {
			$task->documents()->attach( $request->input( 'file.id' ), [ 'is_final' => $final ] );
		}
		return response()->json([]);
	}

}
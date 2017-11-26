<?php

namespace Inspirium\TaskManagement\Controllers\Api;

use Inspirium\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inspirium\Models\HumanResources\Department;
use Inspirium\Models\HumanResources\Employee;
use Inspirium\TaskManagement\Models\Task;

class TaskController extends Controller {

	public function getAllUserTasks() {
		$employee = Auth::user();
		$new_tasks = Task::whereHas('thread',function($query) use ($employee) {
			$query->whereHas('users', function($query) use ($employee) {
				$query->where('employee_id', $employee->id);
			});
		})->where('status', 'new')->with('assigner')->get();

		$accepted_tasks = Task::whereHas('thread',function($query) use ($employee) {
			$query->whereHas('users', function($query) use ($employee) {
				$query->where('employee_id', $employee->id);
			});
		})->where('status', 'accepted')->with('assigner')->get();
		$sent_tasks = Task::where('assigner_id', $employee->id)->with('assigner')->get();
		$rejected_tasks = Task::whereHas('thread',function($query) use ($employee) {
			$query->whereHas('users', function($query) use ($employee) {
				$query->where('employee_id', $employee->id);
			});
		})->where('status', 'rejected')->with('assigner')->get();
		$completed_tasks = Task::whereHas('thread',function($query) use ($employee) {
			$query->whereHas('users', function($query) use ($employee) {
				$query->where('employee_id', $employee->id);
			});
		})->where('status', 'completed')->with('assigner')->get();
		return response()->json(['new_tasks' => $new_tasks, 'accepted_tasks' => $accepted_tasks, 'sent_tasks' => $sent_tasks, 'rejected_tasks' => $rejected_tasks, 'completed_tasks' => $completed_tasks]);
	}

	public function getTask($id) {
		$task = Task::with(['assigner', 'assignee', 'related', 'thread'])->find($id);
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
		$assigner = Auth::user();
		$task->assigner()->associate($assigner);
		$task->type = $request->input('type');
		$task->description = $request->input('description');
		$task->priority = $request->input('priority');
		$deadline = Carbon::createFromFormat('d. m. Y.', $request->input('deadline'));
		$task->deadline = $deadline->toDateTimeString();
		$task->status = 'new';
		$task->assignee_id = $request->input('users')[0]['id'];
		$task->save();
		$task->assignThread($request->input('users'));
		return response()->json([]);
	}

	public function reassignTask( Request $request, $id) {
		$task = Task::find($id);
		$employees = array_pluck($request->input('employees'), 'id');
		$task->thread->users()->attach( $employees );
		$task->assignee_id = $employees[0];
		$task->save();
		$task->load(['thread', 'assignee', 'assigner']);
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
		$new_tasks = $department->tasks->filter(function($value, $key) {
			return $value->status === 'new';
		});
		$old_tasks = $department->tasks->filter(function($value, $key) {
			return $value->status !== 'new';
		});
		return response()->json(['new_tasks' => $new_tasks, 'old_tasks' => $old_tasks]);
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
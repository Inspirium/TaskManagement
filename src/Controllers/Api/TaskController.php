<?php

namespace Inspirium\TaskManagement\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inspirium\HumanResources\Models\Department;
use Inspirium\HumanResources\Models\Employee;
use Inspirium\TaskManagement\Models\Task;

class TaskController extends Controller {

	public function getAllUserTasks() {
		$user_id = Auth::id();
		$employee = Employee::where('user_id', $user_id)->first();
		$new_tasks = $employee->tasks->filter(function($value, $key) {
			return $value->status === 'new';
		});

		$accepted_tasks = $employee->tasks->filter(function($value, $key) {
			return $value->status === 'accepted';
		});
		$sent_tasks = Task::where('assigner_id', $employee->id)->with('assigner')->get();
		$rejected_tasks = $employee->tasks->filter(function($value, $key) {
			return $value->status === 'rejected';
		});
		$completed_tasks = $employee->tasks->filter(function($value, $key) {
			return $value->status === 'completed';
		});
		return response()->json(['new_tasks' => $new_tasks, 'accepted_tasks' => $accepted_tasks, 'sent_tasks' => $sent_tasks, 'rejected_tasks' => $rejected_tasks, 'completed_tasks' => $completed_tasks]);
	}

	public function getTask($id) {
		$task = Task::with(['assigner', 'related', 'thread'])->find($id);
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
		$assigner = Employee::where('user_id', Auth::id())->first();
		$task->assigner()->associate($assigner);
		$task->type = $request->input('type');
		$task->description = $request->input('description');
		$task->priority = $request->input('priority');
		$deadline = Carbon::createFromFormat('d. m. Y.', $request->input('deadline'));
		$task->deadline = $deadline->toDateTimeString();
		$task->status = 'new';
		$task->save();
		if ( $request->has('users') && !empty($request->input('users')) ) {
			$users = array_pluck($request->input('users'), 'id');
			$task->employees()->sync( $users );
			$task->triggerAssigned();
		}
		else {
			$task->employees()->attach($assigner);
			$task->triggerAssigned();
		}
		return response()->json([]);
	}

	public function reassingTask( Request $request, $id) {
		$old_task = Task::find($id);
		$task = new Task();
		$task->name = $old_task->name;
		$assigner = Employee::where('user_id', Auth::id())->first();
		$task->assigner()->associate($assigner);
		$task->type = $old_task->type;
		$task->description = $old_task->description;
		$task->priority = $old_task->priority;
		$task->deadline = $old_task->deadline;
		$task->status = 'new';
		$task->parent()->associate($old_task);
		$task->save();
		$users = array_pluck($request->input('employees'), 'id');
		$task->employees()->sync( $users );
		$task->triggerAssigned();
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
		return response()->json([]);
	}

	public function updateOrder(Request $request) {
		$employee = Employee::where('user_id', Auth::id())->first();
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

}
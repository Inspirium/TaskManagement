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
		$old_tasks = $employee->tasks->filter(function($value, $key) {
			return $value->status !== 'new';
		});
		return response()->json(['new_tasks' => $new_tasks, 'old_tasks' => $old_tasks]);
	}

	public function getTask($id) {
		$task = Task::with(['assigner', 'related'])->find($id);
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

	public function acceptTask($id){
		$task = Task::find($id);
		$task->status = 'old';
		$task->save();
		if ($task->type==3) {
			$task->related->approveRequest();
		}
		return response()->json([]);
	}

	public function rejectTask($id) {
		$task = Task::find($id);
		$task->status = 'old';
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

}
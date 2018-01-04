<?php
namespace Inspirium\TaskManagement\Controllers\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inspirium\Http\Controllers\Controller;
use Inspirium\Models\HumanResources\Department;
use Inspirium\Models\HumanResources\Employee;
use Inspirium\TaskManagement\Models\Task;

class DepartmentController extends Controller {
	public function employeeTasks(Request $request, Employee $employee) {
		$limit = $request->input('limit');
		$offset = $request->input('offset');
		$order = $request->input('order');
		$sort = $request->input('sort');
		if (!$sort) {
			$sort = \Auth::user()->can( 'requestTaskOrder', $employee->department ) ? 'order' : 'new_order';
		}
		if (!$order) {
			$order = 'asc';
		}
		$tasks = Task::where('assignee_id', $employee->id)->with(['assigner', 'assignee'])->limit($limit)->offset($offset)->orderBy($sort, $order)->get();
		$total = Task::whereAssigneeId($employee->id)->count();
		return response()->json(['tasks' => $tasks, 'total' => $total]);
	}

	public function getDepartment(Department $department) {
		$employees = $department->employees()->get();
		return response()->json(['department' => $department, 'employees' => $employees]);
	}

	public function updateOrder(Request $request) {
		$employee = Employee::find($request->input('employee'));
		try {
			$this->authorize( 'approveTaskOrder', $employee );
		}
		catch (AuthorizationException $e) {
			return response()->json(['error' => 'unauthorized'], 403);
		}
		$i = 1 ;
		foreach ($request->input('tasks') as $task_id) {
			$task = Task::find($task_id);
			$task->order = $i;
			$task->new_order = $i;
			$task->save();
			$i++;
		}
		return response()->json([]);
	}

	public function rejectOrder(Request $request) {
		$employee = Employee::find($request->input('employee'));
		try {
			$this->authorize( 'approveTaskOrder', $employee );
		}
		catch (AuthorizationException $e) {
			return response()->json(['error' => 'unauthorized'], 403);
		}
		foreach ($request->input('tasks') as $task_id) {
			$task = Task::find($task_id);
			$task->new_order = $task->order;
			$task->save();
		}
		return response()->json([]);
	}

	public function requestOrder(Request $request) {
		$employee = Employee::find($request->input('employee'));
		try {
			$this->authorize( 'requestTaskOrder', $employee );
		}
		catch (AuthorizationException $e) {
			return response()->json(['error' => 'unauthorized'], 403);
		}
		$i = 1;
		foreach ($request->input('tasks') as $task_id) {
			$task = Task::find($task_id);
			$task->new_order = $i;
			$task->save();
			$i++;
		}
		$task = new Task();
		$task->name = __('Task order approval request');

		$task->type = 6;
		$task->description = $request->input('task.description');
		$task->priority = 'medium';
		$task->status = 'new';
		$task->assigner()->associate(Auth::user());
		$assignee = Employee::find($request->input('task.employees')[0]['id']);
		$task->order = $assignee->tasks->count() + 1;
		$task->new_order = $assignee->tasks->count() + 1;
		$task->related_link = '/tasks/department/' . $employee->department_id . '/#employee-'.$employee->id;
		$task->assignee()->associate($assignee);
		$task->department_id = $assignee->department_id;
		$task->save();
		$task->assignNewThread();
		return response()->json([]);
	}
}
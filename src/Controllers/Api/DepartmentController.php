<?php
namespace Inspirium\TaskManagement\Controllers\Api;

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
		$tasks = Task::where('assignee_id', $employee->id)->with(['assigner', 'assignee'])->limit($limit)->orderBy($sort, $order)->get();
		$total = Task::whereAssigneeId($employee->id)->count();
		return response()->json(['tasks' => $tasks, 'total' => $total]);
	}

	public function getDepartment(Department $department) {
		$employees = $department->employees()->get();
		return response()->json(['department' => $department, 'employees' => $employees]);
	}
}
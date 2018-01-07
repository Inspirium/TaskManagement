<?php
namespace Inspirium\TaskManagement\Controllers\Api;

use Illuminate\Http\Request;
use Inspirium\Http\Controllers\Controller;
use Inspirium\Models\HumanResources\Employee;
use Inspirium\TaskManagement\Models\Task;

class EmployeeController extends Controller {
	public function employeeTasks(Request $request, Employee $employee, $type) {
		$limit = $request->input('limit');
		$offset = $request->input('offset');
		$order = $request->input('order');
		$sort = $request->input('sort');

		if ('sent' === $type) {
			$tasks = Task::with(['assigner', 'assignee'])->where('assigner_id', $employee->id)->orderBy($sort?$sort:'id', $order?$order:'asc')->limit($limit)->offset($offset)->get();
		}
		else {
			$tasks = Task::with( [
				'assigner',
				'assignee'
			] )->where( 'assignee_id', $employee->id )
			             ->where( 'status', $type )->orderBy($sort?$sort:'id', $order?$order:'asc')->limit($limit)->offset($offset)->get();
		}
		return response()->json(['rows' => $tasks, 'total' => count($tasks)]);
	}
}
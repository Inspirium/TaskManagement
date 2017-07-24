<?php

namespace Inspirium\TaskManagement\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inspirium\TaskManagement\Models\Task;

class TaskController extends Controller {

	public function getAllUserTasks() {
		$user_id = Auth::id();
		$tasks = Auth::user()->tasks;
		return response()->json(['tasks' => $tasks]);
	}

	public function getTask($id) {
		$task = Task::with(['assigner'])->find($id);
		return response()->json(['task' => $task]);
	}

	public function postTask(Request $request, $id = null) {
		if ($id) {
			$task = Task::find($id);
		}
		else {
			$task = new Task();
		}
		$task->name = $request->input('name');
		$task->assigner_id = Auth::id();
		$task->type = $request->input('type');
		$task->description = $request->input('description');
		$task->priority = $request->input('priority');
		$deadline = Carbon::createFromFormat('d. m. Y.', $request->input('deadline'));
		$task->deadline = $deadline->toDateTimeString();
		$task->save();
		if ( $request->has('users') && !empty($request->input('users')) ) {
			$task->users()->sync( $request->input( 'users' ) );
		}
		else {
			$task->users()->attach(Auth::id());
		}
		return response()->json([]);
	}

}
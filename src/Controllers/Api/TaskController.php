<?php

namespace Inspirium\TaskManagement\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inspirium\TaskManagement\Models\Task;

class TaskController extends Controller {

	public function getAllUserTasks() {
		$user_id = Auth::id();
		$tasks = Task::users()->where('id', $user_id)->get();
		return response()->json(['tasks' => $tasks]);
	}

	public function getTask($id) {
		$task = Task::find($id);
		return response()->json(['task' => $task]);
	}

	public function postTask(Request $request, $id = null) {
		if ($id) {
			$task = Task::find($id);
		}
		else {
			$task = new Task();
		}

		$task->assigner_id = Auth::id();
		$task->type = $request->input('type');
		$task->description = $request->input('description');
		$task->priority = $request->input('priority');
		if ($request->has('users')) {
			$task->users->sync( $request->input( 'users' ) );
		}
		else {
			$task->users->sync([Auth::id()]);
		}
		$task->save();
		return response()->json([]);
	}

}
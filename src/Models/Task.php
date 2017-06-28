<?php

namespace Inspirium\TaskManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Task
 * @package Inspirium\TaskManagement\Models
 *
 * @property $id
 * @property $type
 * @property $description
 * @property $related_object
 * @property $related_object_id
 */
class Task extends Model {

	use SoftDeletes;

	protected $table = 'tasks';

	public function users() {
		return $this->belongsToMany('Inspirium\UserManagement\Models\User', 'tasks_users', 'user_id', 'task_id');
	}
}
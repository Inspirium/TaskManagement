<?php

namespace Inspirium\TaskManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Task
 * @package Inspirium\TaskManagement\Models
 *
 * @property $id
 * @property $name
 * @property $type
 * @property $description
 * @property $priority
 * @property $status
 * @property $assigner_id
 * @property $related
 * @property $deadline
 */
class Task extends Model {

	use SoftDeletes;

	protected $table = 'tasks';

	protected $appends = ['type'];

	protected $guarded = [];

	public function assigner() {
		return $this->belongsTo('Inspirium\UserManagement\Models\User', 'assigner_id');
	}

	public function users() {
		return $this->belongsToMany('Inspirium\UserManagement\Models\User', 'tasks_users', 'task_id', 'user_id');
	}

	public function documents() {
		return $this->belongsToMany('Inspirium\FileManagement\Models\Document', 'tasks_documents', 'user_id', 'task_id');
	}

	public function related() {
		return $this->morphTo();
	}

	public function getTypeAttribute() {
		return 2;
	}
}
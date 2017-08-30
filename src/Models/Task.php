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

	protected $guarded = [];

	protected $appends = ['related_link'];

	public function assigner() {
		return $this->belongsTo('Inspirium\HumanResources\Models\Employee', 'assigner_id');
	}

	public function employees() {
		return $this->belongsToMany('Inspirium\HumanResources\Models\Employee', 'employee_task_pivot', 'task_id', 'employee_id')->withPivot('order');
	}

	public function departments() {
		return $this->belongsToMany('Inspirium\HumanResources\Models\Department', 'department_task_pivot', 'task_id', 'department_id')->withPivot('order');
	}

	public function documents() {
		return $this->belongsToMany('Inspirium\FileManagement\Models\Document', 'tasks_documents', 'user_id', 'task_id');
	}

	public function related() {
		return $this->morphTo();
	}

	public function getTypeAttribute($value) {
		if ($value){
			return $value;
		}
		return 2;
	}

	public function getRelatedLinkAttribute() {
		if ($this->related_id) {
			return url('proposition/' . $this->related_id . '/start');
		}
		return '';
	}
}
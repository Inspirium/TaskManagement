<?php

namespace Inspirium\TaskManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Inspirium\TaskManagement\Models\Task
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $type
 * @property string|null $description
 * @property string|null $priority
 * @property string|null $status
 * @property int|null $assigner_id
 * @property int|null $related_id
 * @property string|null $related_type
 * @property string|null $deadline
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Inspirium\HumanResources\Models\Employee|null $assigner
 * @property-read \Illuminate\Database\Eloquent\Collection|\Inspirium\HumanResources\Models\Department[] $departments
 * @property-read \Illuminate\Database\Eloquent\Collection|\Inspirium\FileManagement\Models\Document[] $documents
 * @property-read \Illuminate\Database\Eloquent\Collection|\Inspirium\HumanResources\Models\Employee[] $employees
 * @property-read mixed $related_link
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $related
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Inspirium\TaskManagement\Models\Task onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereAssignerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereRelatedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Inspirium\TaskManagement\Models\Task withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Inspirium\TaskManagement\Models\Task withoutTrashed()
 * @mixin \Eloquent
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
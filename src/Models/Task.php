<?php

namespace Inspirium\TaskManagement\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Inspirium\Models\Messaging\Thread;
use Inspirium\TaskManagement\Notifications\TaskAssigned;

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
 * @property string|null $related_link
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Inspirium\HumanResources\Models\Employee|null $assigner
 * @property-read \Illuminate\Database\Eloquent\Collection|\Inspirium\HumanResources\Models\Department[] $departments
 * @property-read \Illuminate\Database\Eloquent\Collection|\Inspirium\FileManagement\Models\Document[] $documents
 * @property-read \Illuminate\Database\Eloquent\Collection|\Inspirium\HumanResources\Models\Employee[] $employees
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
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereRelatedLink($value)
 * @property string|null $status_info
 * @property int|null $parent_id
 * @property-read \Inspirium\TaskManagement\Models\Task|null $parent
 * @property-read \Inspirium\Messaging\Models\Thread $thread
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereStatusInfo($value)
 */
class Task extends Model {

	use SoftDeletes;

	protected $table = 'tasks';

	protected $guarded = [];

	protected $observables = ['assigned'];

	protected $casts = [
		'type' => 'integer'
	];

	protected $dates = [
		'created_at',
		'updated_at',
		'deleted_at',
		'running_from'
	];

	protected $with = [ 'documents'];

	protected $appends = ['files'];

	public function assigner() {
		return $this->belongsTo('Inspirium\Models\HumanResources\Employee', 'assigner_id');
	}

	public function assignee() {
		return $this->belongsTo('Inspirium\Models\HumanResources\Employee', 'assignee_id');
	}

	public function departments() {
		return $this->belongsToMany('Inspirium\Models\HumanResources\Department', 'department_task_pivot', 'task_id', 'department_id')->withPivot('order');
	}

	public function documents() {
		return $this->belongsToMany('Inspirium\Models\FileManagement\File', 'tasks_documents', 'document_id', 'task_id')->withPivot('is_final');
	}

	public function related() {
		return $this->morphTo();
	}

	public function thread() {
		return $this->morphOne('Inspirium\Models\Messaging\Thread', 'connection');
	}

	public function getTypeAttribute($value) {
		if ($value){
			return $value;
		}
		return 2;
	}

	public function getRelatedLinkAttribute($value) {
		if ($value) {
			return $value;
		}
		if ($this->related_id) {
			return url('proposition/' . $this->related_id . '/edit/start');
		}
		return '';
	}

	public function getRunningElapsedAttribute($value) {
		if ($this->is_running) {
			return $value + Carbon::now()->diffInSeconds($this->running_from);
		}
		return $value;
	}

	public function getFilesAttribute() {
		if ($this->type == 4) {
			$type = $this->related_link;
			return [
				'initial' => $this->related->documents()->wherePivot( 'type', $type )->wherePivot( 'final', false )->get(),
				'final' => $this->related->documents()->wherePivot( 'type', $type )->wherePivot( 'final', true )->get(),
				'path'    => $type
			];
		}
		else {
			$value = $this->documents;

			return [
				'initial' => $value->filter( function ( $element ) {
					return ! $element->is_final;
				} ),
				'final'   => $value->filter( function ( $element ) {
					return $element->is_final;
				} ),
				'path'    => 'tasks'
			];
		}
	}

	public function assignThread($employees) {
		if (!$this->thread) {
			$t = Thread::create(['title' => $this->name]);
			$t->users()->sync(collect($employees)->pluck('id')->all());
			$this->thread()->save($t);
			$t->load('users');
			foreach($t->users as $employee) {
				$employee->notify(new TaskAssigned($this));
			}
		}
		else {
			$this->thread->users()->sync(collect($employees)->pluck('id')->all());
			foreach($this->thread->users as $employee) {
				$employee->notify(new TaskAssigned($this));
			}
		}
	}

	//TODO: create Trait
	public function triggerAssigned() {
		//$this->fireModelEvent('assigned');
	}

	public function triggerCompleted() {
		$this->fireModelEvent('completed');
	}
}
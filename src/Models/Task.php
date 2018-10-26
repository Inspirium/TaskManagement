<?php

namespace Inspirium\TaskManagement\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Inspirium\Models\Messaging\Thread;
use Inspirium\TaskManagement\Notifications\TaskAssigned;
use Inspirium\TaskManagement\Notifications\TaskCompleted;

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
 * @property int|null $order
 * @property int|null $new_order
 * @property int|null $department_id
 * @property int|null $assignee_id
 * @property int|null $thread_id
 * @property int $is_running
 * @property \Carbon\Carbon|null $running_from
 * @property int|null $running_elapsed
 * @property-read \Inspirium\Models\HumanResources\Employee|null $assignee
 * @property-read \Inspirium\Models\HumanResources\Department|null $department
 * @property-read mixed $files
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereAssigneeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereIsRunning($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereNewOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereRunningElapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereRunningFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Inspirium\TaskManagement\Models\Task whereThreadId($value)
 */
class Task extends Model {

	use SoftDeletes;

	private $types = [
		1 => [
			'title'     => 'Proposition task',
			'className' => 'tasktype-1'
		],
		2 => [
			'title'     => 'Assignment',
			'className' => 'tasktype-2'
		],
		3 => [
			'title'     => 'Approval Request',
			'className' => 'tasktype-3'
		],
		4 => [
			'title'     => 'Proposition task',
			'className' => 'tasktype-1'
		],
		5 => [
			'title'     => 'Proposition Approval',
			'className' => 'tasktype-5'
		],
		6 => [
			'title'     => 'Task order',
			'className' => 'tasktype-6'
		]
	];

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

	protected $appends = ['files', 'link', 'formatted_type'];

	public function assignee() {
		return $this->belongsTo('Inspirium\Models\HumanResources\Employee', 'assignee_id');
	}

	public function assigner() {
		return $this->belongsTo('Inspirium\Models\HumanResources\Employee', 'assigner_id');
	}

	public function department() {
		return $this->belongsTo('Inspirium\Models\HumanResources\Department', 'department_id');
	}

	public function documents() {
		return $this->belongsToMany('Inspirium\Models\FileManagement\File', 'tasks_documents', 'task_id', 'document_id')->withPivot('is_final');
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

	public function getLinkAttribute() {
		return '/task/show/' . $this->id;
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
			$type = last(explode('/', $this->related_link));
			if ($this->related) {
                return [
                    'initial' => $this->related->documents()->wherePivot('type', $type)->wherePivot('final', false)->get(),
                    'final' => $this->related->documents()->wherePivot('type', $type)->wherePivot('final', true)->get(),
                    'path' => $type
                ];
            }
            else {
                return [
                    'path' => $type
                ];
            }
		}
		else {
			return [
				'initial' => $this->documents()->wherePivot('is_final', false)->get(),
				'final'   => $this->documents()->wherePivot('is_final', true)->get(),
				'path'    => 'tasks'
			];
		}
	}

	public function getFormattedTypeAttribute() {
		return $this->types[$this->type];
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

	public function assignNewThread() {
		$t = Thread::create(['title' => __('Task: :name', ['name' => $this->name])]);
		$t->users()->sync([$this->assignee_id, $this->assigner_id]);
		$this->thread()->save($t);
		$this->assignee->notify(new TaskAssigned($this));
	}

	//TODO: create Trait
	public function triggerAssigned() {
		$this->assignee->notify(new TaskAssigned($this));
	}

	public function triggerCompleted() {
		$this->assigner->notify(new TaskCompleted($this));
	}
}
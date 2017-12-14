<?php

namespace Inspirium\TaskManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Inspirium\TaskManagement\Models\Task;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskAssigned extends Notification
{
    use Queueable;

    private $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
	    switch($this->task->type) {
		    case 1:
			    return [
				    'title' => __('New task assigned'),
				    'message' => __(':assigner has assigned you a new task :task', ['assigner' => $this->task->assigner->name, 'task' => $this->task->name]),
				    'tasktype' => 'assignment',
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
			    break;
		    case 2:
			    return [
				    'title' => __('New task assigned'),
				    'message' => __(':assigner has assigned you a new task :task', ['assigner' => $this->task->assigner->name, 'task' => $this->task->name]),
				    'link' => '/task/show/'.$this->task->id,
				    'tasktype' => 'assignment',
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
			    break;
		    case 3:
			    return [
				    'title' => __('User requested cost approval'),
				    'message' => __(':assigner has requested cost approval on :related', ['assigner' => $this->task->assigner->name, 'related' => $this->task->related->name]),
				    'link' => '/task/show/'.$this->task->id,
				    'tasktype' => 'cost',
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
			    break;
		    case 4:
			    return [
				    'title' => __('Document uploaded'),
				    'message' => __(':assigner has uploaded a document in :related', ['assigner' => $this->task->assigner->name, 'related' => $this->task->name]),
				    'link' => '/task/show/'.$this->task->id,
				    'tasktype' => 'document',
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
			    break;
		    case 5:
			    return [
				    'title' => __('Proposition Approval request'),
				    'message' => __(':assigner has requested Proposition Approval', ['assigner' => $this->task->assigner->name]),
				    'link' => '/task/show/'.$this->task->id,
				    'tasktype' => 'approval',
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
			    break;
		    case 6:
		    	return [
				    'title' => __('Task Order Approval Request'),
				    'message' => __(':assigner has requested task order approval', ['assigner' => $this->task->assigner->name]),
				    'link' => $this->task->related_link,
				    'tasktype' => 'approval',
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
		    	break;
	    }
    }

    public function toBroadcast($notifiable) {
    	return new BroadcastMessage([ 'data' => $this->toArray($notifiable)]);
    }
}

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
				    'title' => 'User assigned you new task',
				    'message' => $this->task->assigner->name . ' je zadao/la novi zadatak - ' . $this->task->name,
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
			    	'title' => 'User assigned you new task',
				    'message' => $this->task->assigner->name . ' je zadao/la novi zadatak - ' . $this->task->name,
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
				    'title' => 'User requested cost approval',
				    'message' => $this->task->assigner->name . ' je zatražio/la odobrenje troška - ' . $this->task->related->name,
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
		    	break;
		    case 4:
		    	return [
				    'title' => 'Document uploaded',
				    'message' => $this->task->assigner->name . ' je prenio dokument ',
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
		    	break;
		    case 5:
			    return [
				    'title' => 'Proposition Approval request',
				    'message' => $this->task->assigner->name . ' je zatražio/la odobrenje propozicije ',
				    'link' => '/task/show/'.$this->task->id,
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
	    switch($this->task->type) {
		    case 1:
			    return new BroadcastMessage([ 'data' => [
				    'title' => 'User assigned you new task',
				    'message' => $this->task->assigner->name . ' je zadao/la novi zadatak - ' . $this->task->name,
				    'tasktype' => 'assignment',
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ]
			    ]);
		    	break;
		    case 2:
			    return new BroadcastMessage([ 'data' => [
				    'title' => 'User assigned you new task',
				    'message' => $this->task->assigner->name . ' je zadao/la novi zadatak - ' . $this->task->name,
				    'tasktype' => 'assignment',
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ]
			    ]);
		    	break;
		    case 3:
			    return new BroadcastMessage([ 'data' => [
				    'title' => 'User requested cost approval',
				    'message' => $this->task->assigner->name . ' je zatražio/la odobrenje troška - ' . $this->task->related->name,
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ]
			    ]);
		    	break;
		    case 4:
			    return new BroadcastMessage([ 'data' => [
				    'title' => 'Document uploaded',
				    'message' => $this->task->assigner->name . ' je prenio dokument',
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ]
			    ]);
			    break;
		    case 5:
			    return new BroadcastMessage([ 'data' => [
				    'title' => 'Proposition Approval request',
				    'message' => $this->task->assigner->name . ' je zatražio/la odobrenje propozicije ',
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ]
			    ]);
			    break;
	    }

    }
}

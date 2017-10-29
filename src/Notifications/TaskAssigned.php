<?php

namespace Inspirium\TaskManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Inspirium\TaskManagement\Models\Task;

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
        return ['database'];
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
				    'message' => 'Please add more data',
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
				    'message' => 'Please add more data',
				    'tasktype' => 'assignment',
				    'link' => '/task/show/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
		    	break;
		    case 3:
			    return [
				    'message' => 'Expense Approval Request',
				    'tasktype' => 'approval_request',
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
}

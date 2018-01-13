<?php

namespace Inspirium\TaskManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Inspirium\TaskManagement\Models\Task;

class TaskOrderApproved extends Notification
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
	    return [
	    	'title' => __('Task order has been approved'),
		    'message' => __(':assignee has approved task order for :employee', ['assignee' => $this->task->assignee->name, 'employee' => $this->task->related->name]),
		    'link' => '/task/show/'.$this->task->id,
		    'tasktype' => $this->task->formatted_type,
		    'sender' => [
			    'name' => $this->task->assignee->name,
			    'image' => $this->task->assignee->image,
			    'link' => $this->task->assignee->link
		    ]
	    ];

    }

	public function toBroadcast($notifiable)
	{
		return new BroadcastMessage([ 'data' => $this->toArray($notifiable) ]);

	}
}

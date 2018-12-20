<?php

namespace Inspirium\TaskManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Inspirium\Models\HumanResources\Employee;
use Inspirium\TaskManagement\Models\Task;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskDeleted extends Notification
{
    use Queueable;

    private $task;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task, Employee $user)
    {
        $this->task = $task;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $notifications = $notifiable->notifications;
        $out = ['database', 'broadcast'];
        if ( $notifications === 1 || (isset($notifications['task_deleted']) && $notifications['task_deleted'])) {
            $out[] = 'mail';
        }
        return $out;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $values = $this->toArray($notifiable);
        return (new MailMessage)
            ->line($values['title'])
            ->line($values['message'])
            ->action('View', url($values['link']));
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
		    'title' => __('Task has been deleted'),
		    'message' => __(':user has deleted a task :task', ['user' => $this->user->name, 'task' => $this->task->name]),
		    'tasktype' => $this->task->formatted_type,
		    'link' => '/tasks',
		    'sender' => [
			    'name' => $this->user->name,
			    'image' => $this->user->image,
			    'link' => $this->user->link
		    ]
	    ];

    }

    public function toBroadcast($notifiable) {
    	return new BroadcastMessage([ 'data' => $this->toArray($notifiable)]);
    }
}

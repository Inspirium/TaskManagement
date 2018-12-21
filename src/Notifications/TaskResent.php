<?php

namespace Inspirium\TaskManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Inspirium\Models\HumanResources\Employee;
use Inspirium\TaskManagement\Models\Task;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskResent extends Notification
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
        $notifications = $notifiable->notification_settings;
        $out = ['database', 'broadcast'];
        if ( $notifications === 1 || (isset($notifications['task_resent']) && $notifications['task_resent'])) {
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
		    'title' => __('Task has been resent'),
		    'message' => __(':user has reactivated a task :task', ['user' => $this->user->name, 'task' => $this->task->name]),
		    'tasktype' => $this->task->formatted_type,
		    'link' => '/task/show/'.$this->task->id,
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

<?php

namespace Inspirium\TaskManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Inspirium\Models\WorkOrder;

class WorkOrderAssigned extends Notification
{
    use Queueable;

    private $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(WorkOrder $task)
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
        $notifications = $notifiable->notification_settings;
        $out = ['database', 'broadcast'];
        if ( $notifications === 1 || (isset($notifications['task_assigned']) && $notifications['task_assigned'])) {
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
				    'title' => __('New work order assigned'),
				    'message' => __(':assigner has assigned you a new work order :task', ['assigner' => $this->task->assigner->name, 'task' => $this->task->name]),
				    'tasktype' => 'tasktype-1',
				    'link' => '/task/'.$this->task->id,
				    'sender' => [
					    'name' => $this->task->assigner->name,
					    'image' => $this->task->assigner->image,
					    'link' => $this->task->assigner->link
				    ]
			    ];
    }

    public function toBroadcast($notifiable) {
    	return new BroadcastMessage([ 'data' => $this->toArray($notifiable)]);
    }
}

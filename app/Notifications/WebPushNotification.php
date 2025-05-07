<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WebPushNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $title, $icon, $body, $action;

    public function __construct($title, $icon, $body, $action = [])
    {        
        $this->title = $title;
        $this->icon = $icon;
        $this->body = $body;
        \Log::info('Log starts in constructor');
        \Log::info($this->title);
        \Log::info($this->icon);
        \Log::info($this->body);
        \Log::info('Log ends in constructor');
        $this->action = implode(',', $action);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {        
        
        \Log::info($this->title);
        \Log::info($this->body);
        return (new WebPushMessage)
            ->title($this->title)
            ->icon($this->icon)
            ->body($this->body);
            //->action('View App', 'notification_action');
    }
}

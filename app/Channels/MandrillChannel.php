<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

/**
 * Class MandrillChannel
 * @package App\Channels
 */
class MandrillChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toMandrill($notifiable);

        // Send notification to the $notifiable instance...
    }
}

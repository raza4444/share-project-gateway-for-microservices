<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Mandrill;
use Mandrill_Error;

/**
 * Class MailChimpChannel
 * @package App\Channels
 */
class MailChimpChannel
{
    /** @var \Mandrill */
    protected $mandrill;

    /**
     * Constructs an instance of the channel.
     *
     * @param \Mandrill $mandrill
     */
    public function __construct(Mandrill $mandrill)
    {
        $this->mandrill = $mandrill;
    }

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
        $recipient = $notifiable->routeNotificationForMandrill();

        $message = $notification->toMandrill($notifiable)
            ->to($recipient)
            ->toArray();

        $message = $this->buildMessage($message);

        try {
            $this->mandrill->messages->sendTemplate($message['template_name'], [], $message);

            Log::info("[MailChimpChannel] : email with template name : {$message['template_name']} has been sent.");

        } catch (Mandrill_Error $e) {

            Log::error($e->getMessage());

        }
    }

    protected function buildMessage($message): array
    {
        Arr::set($message, 'from_email', "vakman@zoofy.nl");

        if (empty($message['from_name'])) {
            Arr::set($message, 'from_name', config('mail.from.name'));
        }

        Arr::set($message, 'bcc_address', null);

        Arr::set($message, 'headers', ['Reply-To' => "vakman@zoofy.nl"]);

        Arr::set($message, 'global_merge_vars', $this->buildData($message['global_merge_vars']));
        Arr::set($message, 'merge_language', 'handlebars');
        Arr::set($message, 'template_content', []);

        return $message;
    }

    protected function buildData(array $data)
    {
        return collect($data)
            ->map(function ($item, $key) {
                return [
                    'name' => $key,
                    'content' => $item
                ];
            })
            ->values()
            ->toArray();
    }

}

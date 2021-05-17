<?php

namespace App\Listeners;

use App\Events\SignupEmailEvent;
use App\Services\SignupVerificationService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SignupEmailListner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SignupEmailEvent $event)
    {
        $verification = new SignupVerificationService($event->user);
        $verification->sendVerificationEmail();

        if ($event->user->phone_number) {
            $verification->sendOTPSMS();
        }
    }
}

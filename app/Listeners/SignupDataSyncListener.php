<?php

namespace App\Listeners;

use App\Events\SignupDataSyncEvent;
use App\Services\SignupDataSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SignupDataSyncListener
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
     * @param  SignupDataSyncEvent  $event
     * @return void
     */
    public function handle(SignupDataSyncEvent $event)
    {
        $signup = new SignupDataSyncService($event->user);
        $signup->syncSignupData();
    }

    /**
     * Handle a job failure.
     *
     * @param  \App\Events\SignupDataSyncEvent  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(OrderShipped $event, $exception)
    {
        //
    }
}

<?php

namespace App\Providers;

use App\Channels\MailChimpChannel;
use App\Services\Mail\Mandrill\MandrillMailApi;
use Illuminate\Support\ServiceProvider;
use Mandrill;

/**
 * Class MandrillServiceProvider
 * @package App\Providers
 */
class MandrillServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Mandrill', function ($app) {
            return new MandrillMailApi();
        });

        $this->app->when(MailChimpChannel::class)
            ->needs(Mandrill::class)
            ->give(function () {
                $apiKey = config('services.mandrill.secret');
                return new Mandrill($apiKey);
            });
    }
}

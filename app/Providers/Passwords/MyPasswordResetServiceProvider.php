<?php

namespace App\Providers\Passwords;

use Illuminate\Auth\Passwords\PasswordResetServiceProvider;

/**
 * Class MyPasswordResetServiceProvider
 * @package App\Providers\Passwords
 */
class MyPasswordResetServiceProvider extends PasswordResetServiceProvider
{
    /**
     * [Override]
     * Register the password broker instance.
     *
     * @return void
     */
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new MyPasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }
}
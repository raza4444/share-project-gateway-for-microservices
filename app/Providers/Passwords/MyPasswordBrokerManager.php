<?php

namespace App\Providers\Passwords;

use Closure;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Str;
use Illuminate\Auth\Passwords\PasswordBrokerManager;

/**
 * Class MyPasswordBrokerManager
 * @package App\Providers\Passwords
 */
class MyPasswordBrokerManager extends PasswordBrokerManager
{
    /**
     * [Override]
     * Create a token repository instance based on the given configuration.
     *
     * @param  array $config
     *
     * @return TokenRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        $key = $this->app['config']['app.key'];

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $connection = $config['connection'] ?? null;

        return new MyDatabaseTokenRepository(
            $this->app['db']->connection($connection),
            $this->app['hash'],
            $config['table'],
            $key,
            $config['expire']
        );
    }

    /**
     * Send a password reset link to a user.
     *
     * @param array $credentials
     * @return string
     */
    public function sendResetLink(array $credentials)
    {
        return parent::sendResetLink($credentials);
    }

    /**
     * Reset the password for the given token.
     *
     * @param array $credentials
     * @param \Closure $callback
     * @return mixed
     */
    public function reset(array $credentials, Closure $callback)
    {
        return parent::reset($credentials, $callback);
    }

    /**
     * Set a custom password validator.
     *
     * @param \Closure $callback
     * @return void
     */
    public function validator(Closure $callback)
    {
        parent::validator($callback);
    }

    /**
     * Determine if the passwords match for the request.
     *
     * @param array $credentials
     * @return bool
     */
    public function validateNewPassword(array $credentials)
    {
        return parent::validateNewPassword($credentials);
    }
}
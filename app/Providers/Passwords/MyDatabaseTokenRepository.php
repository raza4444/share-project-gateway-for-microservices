<?php

namespace App\Providers\Passwords;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;

/**
 * Class MyDatabaseTokenRepository
 * @package App\Providers\Passwords
 */
class MyDatabaseTokenRepository extends DatabaseTokenRepository
{
    /**
     * [Override]
     * Create a new token for the user.
     *
     * @return string
     * @throws \Exception
     */
    public function createNewToken()
    {
        // Custom Token
        return random_code();
    }
}
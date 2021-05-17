<?php

namespace App\Services;

use \app\User;
use \Firebase\JWT\JWT;

/**
 * Encode and decode JWT service
 * Class UserJWTService
 *  @package App\Services
 */

class UserJWTService
{

    /**
     * JWT Algorithm
     *
     * @var string
     */
    private $jwtAlgo = 'HS256';

    /**
     * Jwt used for encode token
     *
     * @param User|Null $user
     * @return string
     */

    public function encode($user = null): string
    {

        /*
        - iat (issued at time): Time at which the JWT was issued; can be used to determine age of the JWT
        - iss (issuer): Issuer of the JWT
        - exp (expiration time): Time after which the JWT expires
        - user (user detail) current user detail
        */

        $payload = [
            'iat' => time(),
            'iss' => 'gateway',
            'exp' => time() + (15 * 60),
            'user' => $user ? json_encode($user) : null,
        ];

        try {
            return JWT::encode($payload, env('APP_KEY'), $this->jwtAlgo);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Decode jwt user
     *
     * @param string $token
     * @return object
     */
    public function decode(string $token): object
    {
        try {
            $payload = JWT::decode($token, env('APP_KEY'), [$this->jwtAlgo]);
            if (isset($payload)) {
                return json_decode($payload->user);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * validate JWT token
     *
     * @param string $token
     * @return bool
     */
    public function validate(string $token)
    {
        $validToken = false;
        try {
            $payload = JWT::decode($token, env('APP_KEY'), [$this->jwtAlgo]);
            if (isset($payload) && isset($payload->user)) {
                $validToken = true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return $validToken;
    }
}

<?php

namespace App\Traits;

/**
 * Trait ServiceActions
 * @package App\Traits
 */
trait ServiceActions
{

    public function createProvider(string $type)
    {
        return $this->createApi($type);
    }

    public function getApiKey()
    {
        return $this->api_key;
    }
}

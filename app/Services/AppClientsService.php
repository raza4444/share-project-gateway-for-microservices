<?php

namespace App\Services;

use App\Models\OauthClient;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class AppClientsService
 * @package App\Services
 */
class AppClientsService
{
    protected $client;

    /**
     * AppClientsService constructor.
     * @param $client_id
     */
    public function __construct($client_id)
    {
        $client = Redis::get('APP-ID-' . $client_id);
        if ($client === null) {
            $client = OauthClient::where('id', $client_id)->first();
            if ($client === null || $client->revoked) {
                throw new UnauthorizedHttpException(401, 'You are not allowed to access this endpoint.');
            }
            $appClient = $client->toArray();
            Redis::set('APP-ID-' . $client_id, json_encode($appClient), 'EX', 7200);
        } else {
            $appClient = json_decode($client, true);
        }
        $this->client = $appClient;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }
}

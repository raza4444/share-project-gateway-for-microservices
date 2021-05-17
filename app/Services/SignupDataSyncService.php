<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SignupDataSyncService
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * sendVerificationEmail
     *
     * @return void
     */
    public function syncSignupData()
    {
        try {
            Log:info("SignupDataSync: Started: {$this->user}");

            $userArr = json_decode($this->user);

            if ($userArr->role_id == 4) {
                $url =  "/api/v1/customers/profile";
            } elseif ($userArr->role_id == 5) {
                $url =  "/api/v1/partners/profile";
            } else {
                $url =  "";
                return response()->json(['error' => 'Invalid role_id for signup!']);
            }

            $endpoint = env('API_ENDPOINT', 'https://api-dev.zoofy.nl') . $url;
            $client = new \GuzzleHttp\Client();

            $response = $client->request('POST', $endpoint, [
                'headers' => ['Authorization' => "Bearer"],
                'json' => $userArr
            ]);

            $responseJson = $response->getBody();

            if ($response->getStatusCode() === 201) {
                Log::info("SignupDataSync: Success: {$responseJson}");
            } else {
                Log::info("SignupDataSync: Failed with reason: {$responseJson}");
                return response()->json($responseJson);
            }
        } catch (\Exception  $exception) {
            Log::info("SignupDataSync: Failed with exception: {$exception}");
            return response()->json($exception);
        }
    }
}

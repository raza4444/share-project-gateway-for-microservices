<?php

namespace App\Services;

use App\Services\Mail\Mandrill\Professionals\MandrillMessage;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class SignupVerificationService
 * @package App\Services
 */
class SignupVerificationService
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
    public function sendVerificationEmail()
    {
        // send token to user via email
        /*Mail::send('emails.signup-verification', ['user' => $this->user], function ($m) {
            $m->to($this->user->email, $this->user->name)->subject('Zoofy Email Veification Code');
        });*/

        $templateName = 'csa-customer-signup-email';
        $subject = 'Zoofy Email Veification Code';

        $data = [
            ['domain' => env('APP_DOMAIN')],
            ['port' => env('APP_PORT')],
            ['user' => [
                'name' => $this->user->name,
                'email_verification_token' => $this->user->email_verification_token,
                'phone_number' => $this->user->phone_number,
                'country_code' => $this->user->country_code,
            ]]
        ];

        $service = app('Mandrill');

        $service->setAttribute('subject', $subject)
            ->setAttribute('to_mail', $this->user->email)
            ->setAttribute('to_name', $this->user->name);

        $service->addGlobalMergeVarData($data);

        try {
            $message = (new MandrillMessage($service))->useHandleBars()
                ->get();
            (new \Mandrill($service->getApiKey()))->messages->sendTemplate($templateName, [], $message);
        } catch (\Mandrill_Error $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * verifyEmailToken
     *
     * @param mixed $request
     * @return void
     */
    public static function verifyEmailToken($request)
    {
        $email = $request->email;
        $token = $request->token;

        $user = User::where('email', $email)
            ->where('email_verification_token', $token)
            ->update(['email_verified_at' => DB::raw('now()')]);

        return (!$user) ? false : true;
    }

    /**
     * sendOTPSMS
     *
     * @return void
     */
    public function sendOTPSMS()
    {
        $data = [
            'recipient' => $this->user->country_code . $this->user->phone_number,
            'originator' => 'Zoofy',
            'template' => __('auth.token_message')
        ];
        $response = Http::withHeaders([
            'Authorization' => "AccessKey " . env('MESSAGEBIRD_ACCESS_KEY'),
        ])->post(env('MESSAGEBIRD_URL') . '/verify', $data);

        if (!$response->ok()) {
            $response->throw()->json();
        }
        $this->user->otp_assigned_user_id = $response['id'];
        $this->user->increment('otp_retry_count');
        $this->user->save();
    }

    public static function verifySMSOTP($request)
    {
        $email = $request['email'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $data = [
            'token' => $request['token']
        ];
        $response = Http::withHeaders([
            'Authorization' => "AccessKey " . env('MESSAGEBIRD_ACCESS_KEY'),
        ])->get(env('MESSAGEBIRD_URL') . '/verify/' . $user->otp_assigned_user_id, $data);

        if (!$response->ok()) {
            echo env('MESSAGEBIRD_URL') . '/verify/' . $user->otp_assigned_user_id . "?token=" . $request['token'];
            throw new UnprocessableEntityHttpException(
                $response->json()['errors'][0]['description'],
                null,
                $response->status()
            );
            $response->throw();
        }
        $user->phone_number_verified_at = DB::raw('now()');
        $user->save();

        return (!$user) ? false : true;
    }
}

<?php

namespace App\Http\Controllers;

use App\Events\SignupDataSyncEvent;
use App\SocialIdentity;
use DB;
use App\User;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Socialite;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Events\SignupEmailEvent;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignUpRequest;
use App\Services\SignupVerificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class AuthController extends ApiController
{
    /**
     * @param SignupRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUp(SignUpRequest $request)
    {
        $input = request()->validate(
            [
                'name' => 'required',
                'role_id' => 'required|exists:roles,id',
                'email' => 'required|unique:users,email|email',
                'country_code' => 'sometimes|required',
                'phone_number' => 'sometimes|required',
                'password' => 'required',
                'additional' => 'sometimes'
            ]
        );

        try {
            DB::beginTransaction();

            $uuid = Uuid::uuid4()->toString();

            $input['email_verification_token'] = random_code();
            $input['password'] = Hash::make($input['password']);
            $input['uuid'] = $uuid;

            $request->merge(['uuid' => $uuid]);
            $input['additional'] = json_encode($request->all());


            $user = User::create($input);

            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $input['role_id']
            ]);

            event(new SignupEmailEvent($user));

            if ($input['role_id'] == 4 || $input['role_id'] == 5) {
                event(new SignupDataSyncEvent($input['additional']));
            }

            DB::commit();

            return $this->successResponse($user, __('auth.verify_account'));
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(__($e->getMessage()), 422);
        }
    }

    /**
     * resentOTPSMS
     *
     * @param mixed $request
     * @return void
     */
    public function resendOTPSMS(Request $request)
    {
        $request = request()->validate(['phone_number' => 'required']);

        $user = User::where('phone_number', $request['phone_number'])->first();

        if (!$user) {
            return $this->errorResponse(__('auth.missing_user'), 400);
        }

        if ($user->otp_retry_count == 3) {
            return $this->errorResponse(__('auth.otp_throttle'), 400);
        }

        (new SignupVerificationService($user))->sendOTPSMS();
        $data = [
            'country_code' => $user->country_code,
            'phone_number' => $user->phone_number
        ];
        return $this->successResponse($data, __('auth.otp_sent'));
    }

    /**
     * resendEmailToken
     *
     * @param mixed $request
     * @return void
     */
    public function resendEmailToken(Request $request)
    {
        $request = request()->validate(['email' => 'required|email']);

        $user = User::where('email', $request['email'])->first();

        if (!$user) {
            return $this->errorResponse(__('auth.missing_user'), 400);
        }

        $user->email_verification_token = random_code();
        $user->save();

        (new SignupVerificationService($user))->sendVerificationEmail();
        $data = [
            'email' => $user->email
        ];
        return $this->successResponse($data, __('auth.email_token_sent'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        $response = SignupVerificationService::verifyEmailToken($request);
        if ($response) {
            $data = [
                'email' => $request['email']
            ];
            return $this->successResponse($data, __('validation.email-verified'));
        } else {
            return $this->errorResponse(__('validation.invalid-token'), 401);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPhoneNumber(Request $request)
    {
        $response = SignupVerificationService::verifySMSOTP($request);
        if ($response) {
            $data = [
                'email' => $request['email']
            ];
            return $this->successResponse($data, __('validation.phone-number-verified'));
        } else {
            return $this->errorResponse(__('validation.invalid-token'), 401);
        }
    }

    /**
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $checkCredentials = Auth::attempt(['email' => request('email'), 'password' => request('password')]);

        if (!$checkCredentials) {
            $response = [
                'status' => 'Error',
                'message' => 'Incorrect username or password.',
                'data' => [
                    'message' => 'Incorrect username or password.'
                ]
            ];
            return response()->json($response, 403);
        }

        $response = $this->getAccessTokenByUserName($request->get('email'), $request->get('password'));

        if (isset($response->status) && $response->status == 'Error') {
            return $response; // if credentials are not correct.
        } else {
            // check if email is verified.
            $user = User::where('email', $request->get('email'))->first();
            if (!$user->hasVerifiedEmail()) {
                $response = [
                    'message' => 'Email is not verified.'
                ];
                return $this->errorResponse($response, 403);
            } else {
                // credentials are correct and email is verified.
                return $response;
            }
        }
    }


    /**
     * Get the authenticated User
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        return response()->json(auth('api')->user());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $data = request()->validate(
            [
                'name' => 'required',
                'email' => 'required|email',
                'country_code' => 'sometimes|required',
                'phone_number' => 'sometimes|required'
            ]
        );

        // mark email unverified if email is changed.
        if ($request->email != $user->email) {
            $data['email_verified_at'] = null;
        }

        // mark phone number unverified if phone number is changed.
        if ($request->has('phone_number') && $request->phone_number != $user->phone_number) {
            $data['phone_number_verified_at'] = null;
        }

        try {
            $user->update($data);
        } catch (\Exception $e) {
            return $this->errorResponse(__($e->getMessage()), 400);
        }
        return response()->json(compact('user'));
    }

    /**
     *  Logout user (Revoke the token)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     *  Forget Password, send email with token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPassword(Request $request)
    {
        $credentials = request()->validate(['email' => 'required|email']);

        $response = Password::sendResetLink($credentials);
        if ($response == 'passwords.sent') {
            return $this->successResponse(null, __('passwords.sent'));
        } else {
            return $this->errorResponse(__($response), 400);
        }
    }


    /**
     *  Reset Password, verify token and reset the password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $credentials = request()->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return $this->errorResponse(__('passwords.token'), 400);
        }

        $user = User::where('email', $request->email)->first();

        // mark email as verified
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->successResponse(null, __('passwords.success'));
    }


    /**
     *  Change Password, verify old and reset the password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = auth('api')->user();

        if(empty($user)) {
            return $this->errorResponse(__('auth.missing_user'), 401);
        }

        request()->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|string|min:6|required_with:new_password|same:new_password'
        ]);

        if (Hash::check($request->old_password, $user->password)) {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);
        } else {
            return $this->errorResponse(__('passwords.old'), 400);
        }

        return $this->successResponse(null, __('passwords.success'));
    }


    /**
     * @param $userName
     * @param $password
     * @return mixed
     */
    private function getAccessTokenByUserName($userName, $password)
    {
        $data = [
            'grant_type' => 'password',
            'client_id' => $this->appClient['id'],
            'client_secret' => $this->appClient['secret'],
            'username' => $userName,
            'password' => $password,
            'scope' => '*',
        ];

        $response = Http::withHeaders([
            'APP-ID' => $this->appClient['id'],
            'Content-Type' => 'application/json'
        ])->post(env('ZOOFY_GATEWAY') . '/passport/oauth/token', $data);

        if (!$response->ok()) {
            throw new UnprocessableEntityHttpException($response->json()['message'], null, $response->status());
        }

        return $response->json();
    }


    /**
     * getAccessTokenBySocial
     *
     * @param mixed $token
     * @param mixed $provider
     * @return void
     */
    private function getAccessTokenBySocial($token, $provider)
    {
        $data = [
            'grant_type' => 'social',
            'client_id' => $this->appClient['id'],
            'client_secret' => $this->appClient['secret'],
            'provider' => $provider,
            'access_token' => $token,
        ];

        try {
            $response = Http::withHeaders([
                'APP-ID' => $this->appClient['id'],
                'Content-Type' => 'application/json'
            ])->post(env('ZOOFY_GATEWAY') . '/passport/oauth/token', $data);
        } catch (\Exception $exception) {
            $this->errorResponse($exception->getMessage());
        }

        if (!$response->ok()) {
            $this->errorResponse($response->json()['message'], $response->status());
        }

        return $response->json();
    }

    /**
     * Redirect the user to the social authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from social login.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleSocialCallback($provider, Request $request)
    {
        try {

            $user = User::firstOrCreate(
                ['email' => $request->user['email']],
                $request->user
            );

            $providerId = $request->user['id'];
            $providerName = $provider;

            SocialIdentity::firstOrCreate(
                ['provider_id' => $providerId],
                ['user_id' => $user->id, 'provider_name' => $providerName]
            );

            $user->token = $user->createToken($provider, ['*']);

            $response = [
                "token_type" => "Bearer",
                "expires_in" => 1296000,
                "access_token" => $user->token->accessToken,
                "refresh_token" => ""
            ];

            $accessToken = response()->json($response);
            $userData = $request->user;

            //  insert user data in additional column
            $user = User::where('email', $userData['email'])->first();
            $userId = $user->id;
            $uuid = $user->uuid;
            $roleId = 4; // TODO: social login 'role_id=4' is only for consumers
            $userData = $userData->user ?? $userData;
            $userData['role_id'] = $roleId;
            $userData['uuid'] = $uuid;
            $additional = json_encode((array)$userData);
            $user->update(['additional' => $additional]);

            // insert values in user roles table
            UserRole::firstOrCreate(
                [
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]
            );

            event(new SignupDataSyncEvent($additional));

            return $accessToken;
        } catch (Exception $e) {
            return $this->errorResponse(__('passwords.user'), 400);
        }
    }

    /**
     * Obtain the user information from social login.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider, Request $request)
    {
        try {

            $userData = Socialite::driver($provider)->stateless()->userFromToken($request->code);
            $accessToken = $this->getAccessTokenBySocial($userData->token, $provider);

            //  insert user data in additional column
            $user = User::where('email', $userData->email)->first();
            $userId = $user->id;
            $uuid = $user->uuid;
            $roleId = 4; // TODO: social login 'role_id=4' is only for consumers
            $userData = $userData->user ?? $userData;
            $userData['role_id'] = $roleId;
            $userData['uuid'] = $uuid;
            $additional = json_encode((array)$userData);
            $user->update(['additional' => $additional]);

            // insert values in user roles table
            UserRole::firstOrCreate(
                [
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]
            );

            event(new SignupDataSyncEvent($additional));

            return $accessToken;
        } catch (Exception $e) {
            return $this->errorResponse(__('passwords.user'), 400);
        }
    }

    /**
     * Refresh the access token
     * @param Request $request
     */
    public function refreshToken(Request $request)
    {
        $data = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->appClient['id'],
            'client_secret' => $this->appClient['secret'],
            'refresh_token' => $request->refresh_token,
            'scope' => '*',
        ];

        $response = Http::withHeaders([
            'APP-ID' => $this->appClient['id'],
            'Content-Type' => 'application/json'
        ])->post(env('ZOOFY_GATEWAY') . '/passport/oauth/token', $data);

        if (!$response->ok()) {
            throw new UnprocessableEntityHttpException($response->json()['message'], null, $response->status());
        }

        return $response->json();
    }

    /**
     * Create social login user in db
     * @param $provider
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSocialUser($provider, $request)
    {
        try {
            DB::beginTransaction();

            $user = User::firstOrCreate(
                ['email' => $request->user['email']],
                $request->user
            );

            UserRole::firstOrCreate([
                'user_id' => $user->id,
                'role_id' => $request->role_id
            ]);

            SocialIdentity::firstOrCreate(
                ['provider_id' => $request->user['id']],
                ['user_id' => $user->id, 'provider_name' => $provider]
            );

            DB::commit();

            if ($request->role_id == 4 || $request->role_id == 5) {
                event(new SignupDataSyncEvent($request->user['additional']));
            }

            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            return [];
        }
    }

}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/health-check', function () {
    return 'https://gateway-dev.zoofy.nl: API V1.0';
});

Route::delete('/cache', function () {
    Cache::flush();
    return [
        'message' => 'cache has been clear.'
    ];
});

Route::middleware('auth:api')
    ->prefix('auth')
    ->group(function () {
        Route::get('/profile', 'AuthController@profile');
        Route::put('/profile', 'AuthController@updateProfile');
        Route::post('/logout', 'AuthController@logout');
    });

Route::middleware(['auth:api', 'user.admin'])
    ->prefix('services')
    ->group(function () {
        Route::post('/', 'ServiceController@store');
        Route::get('/', 'ServiceController@index');
        Route::post('/{service_id}', 'ServiceController@storeRoutes');
        Route::get('/{service_id}', 'ServiceController@getRoutes');
    });

Route::post('/token/refresh', 'AuthController@refreshToken');
Route::post('/signup', 'AuthController@signUp');

// Sign up verification
Route::prefix('verify')->group(function () {
    Route::post('email', 'AuthController@verifyEmail');
    Route::post('phone_number', 'AuthController@verifyPhoneNumber');
    Route::post('resend_otp_sms', 'AuthController@resendOTPSMS');
    Route::post('resend_email_token', 'AuthController@resendEmailToken');
});

// forget and reset password
Route::post('/forget-password', 'AuthController@forgetPassword');
Route::post('/reset-password', 'AuthController@resetPassword')->name('password.reset');
Route::post('/change-password', 'AuthController@changePassword')->name('password.change');

// login using credentials or social.
Route::prefix('login')->group(function () {
    Route::post('/', 'AuthController@login');
    Route::get('{provider}', 'AuthController@redirectToProvider');
    Route::get('{provider}/callback', 'AuthController@handleProviderCallback');
    Route::post('{provider}/callback', 'AuthController@handleProviderCallback');

    // manual implementation to fix mobile issues
    Route::post('{provider}/social/callback', 'AuthController@handleSocialCallback');
});

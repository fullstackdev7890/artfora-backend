<?php

use App\Http\Controllers\TwoFactorAuthEmailController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SettingController;

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

Route::group(['middleware' => 'auth'], function () {
    Route::post('/users', ['uses' => UserController::class . '@create']);
    Route::put('/users/{id}', ['uses' => UserController::class . '@update']);
    Route::delete('/users/{id}', ['uses' => UserController::class . '@delete']);
    Route::get('/users/{id}', ['uses' => UserController::class . '@get']);
    Route::get('/users', ['uses' => UserController::class . '@search']);
    Route::get('/profile', ['uses' => UserController::class . '@profile']);
    Route::put('/profile', ['uses' => UserController::class . '@updateProfile']);

    Route::post('/media', ['uses' => MediaController::class . '@create']);
    Route::delete('/media/{id}', ['uses' => MediaController::class . '@delete']);
    Route::get('/media', ['uses' => MediaController::class . '@search']);

    Route::put('/settings/{name}', ['uses' => SettingController::class . '@update']);
    Route::get('/settings/{name}', ['uses' => SettingController::class . '@get']);
    Route::get('/settings', ['uses' => SettingController::class . '@search']);

    Route::post('/auth/2fa/sms/send', ['uses' => AuthController::class . '@sendSms']);
    Route::post('/auth/2fa/sms/check', ['uses' => AuthController::class . '@checkSms']);

    Route::post('/auth/2fa/otp/generate', ['uses' => AuthController::class . '@getOtpQrCode']);
    Route::post('/auth/2fa/otp/check', ['uses' => AuthController::class . '@checkOtp']);
});

Route::group(['middleware' => 'guest'], function () {
    Route::post('/auth/login', ['uses' => AuthController::class . '@login']);
    Route::post('/auth/register', [ 'uses' => AuthController::class . '@register' ]);
    Route::post('/auth/forgot-password', ['uses' => AuthController::class . '@forgotPassword']);
    Route::post('/auth/restore-password', ['uses' => AuthController::class . '@restorePassword']);
    Route::post('/auth/token/check', ['uses' => AuthController::class . '@checkRestoreToken']);
    Route::post('/auth/email/verify', ['uses' => AuthController::class . '@verifyEmail', 'as' => 'auth.confirm']);
    Route::post('/auth/2fa/email/resend', ['uses' => AuthController::class . '@resend2FAEmail']);
    Route::post('/auth/2fa/email/check', ['uses' => AuthController::class . '@resend2FAEmail']);
    Route::post('/auth/2fa/sms/confirm', ['uses' => AuthController::class . '@confirmSms']);
    Route::post('/auth/2fa/otp/confirm', ['uses' => AuthController::class . '@confirmOtp']);

    Route::get('/status', ['uses' => StatusController::class . '@status']);
});
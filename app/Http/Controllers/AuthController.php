<?php

namespace App\Http\Controllers;

use App\Facades\OtpTwoFactorAuthorization;
use App\Facades\SmsTwoFactorAuthorization;
use App\Http\Requests\Auth\Check2faEmailRequest;
use App\Http\Requests\Auth\Confirm2faOtpRequest;
use App\Http\Requests\Auth\CheckRestorePasswordTokenRequest;
use App\Http\Requests\Auth\CheckSms2faRequest;
use App\Http\Requests\Auth\Confirm2faEmailRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\GetOtpQrCodeRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\Auth\Resend2faEmailRequest;
use App\Http\Requests\Auth\RestorePasswordRequest;
use App\Http\Requests\Auth\Enable2faSmsRequest;
use App\Http\Requests\Auth\Confirm2faSmsRequest;
use App\Http\Requests\Auth\Check2faOtpRequest;
use App\Mails\AccountConfirmationMail;
use App\Models\User;
use App\Services\TwoFactorAuthEmailService;
use App\Services\UserService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    public function login(
        LoginRequest $request,
        UserService $service,
        TwoFactorAuthEmailService $authEmailService,
        JWTAuth $auth
    ) {
        $login = strtolower($request->input('login'));

        $token = $auth->attempt([
            'email' => $login,
            'password' => $request->input('password')
        ]);

        if ($token === false) {
            return response()->json([
                'message' => __('auth.failed')
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $service->findBy('email', $login);

        if (!$user['email_verified_at']) {
            return response()->json([
                'message' => 'You should verify your account'
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        if ($user['2fa_type'] === User::SMS_2FA_TYPE) {
            SmsTwoFactorAuthorization::verify($user['phone']);

            return response()->json([
                'message' => 'Two factor verification required. Code has been sent',
                'type' => User::SMS_2FA_TYPE,
                'user_id' => $user['id']
            ]);
        }

        if ($user['2fa_type'] === User::OTP_2FA_TYPE) {
            return response()->json([
                'message' => 'Two factor verification required. Please use authorization application',
                'type' => User::OTP_2FA_TYPE,
                'user_id' => $user['id']
            ]);
        }

        $authEmailService->send($user->email);

        return response()->json([
            'message' => 'Two factor verification required. We have sent you an email with 2fa code',
            'type' => User::EMAIL_2FA_TYPE,
            'user_id' => $user['id']
        ]);
    }

    public function register(RegisterUserRequest $request, UserService $service, JWTAuth $auth)
    {
        $data = $request->validated();

        $data['email'] = strtolower($data['email']);

        $user = $service->create($data);

        Mail::queue(new AccountConfirmationMail($user['email'], [
            'user' => $user,
            'hash' => $user['email_verification_token'],
            'redirect' => $request->input('redirect_after_verification', '')
        ]));

        return response([
            'message' => 'User has been successfully created. Confirmation email has been sent'
        ], Response::HTTP_OK);
    }

    public function refreshToken(RefreshTokenRequest $request, UserService $service)
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    public function forgotPassword(ForgotPasswordRequest $request, UserService $service)
    {
        $service->forgotPassword($request->input('login'));

        return response('', Response::HTTP_OK);
    }

    public function restorePassword(RestorePasswordRequest $request, UserService $service)
    {
        $service->restorePassword(
            $request->input('token'),
            $request->input('password')
        );

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function checkRestorePasswordToken(CheckRestorePasswordTokenRequest $request)
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    public function verifyEmail(Confirm2faEmailRequest $request, UserService $service, JWTAuth $auth)
    {
        $user = $service->verifyEmail($request->input('token'));

        $token = $auth->fromUser($user);

        return response()->json([ 'token' => $token ]);
    }

    public function resend2faEmail(Resend2faEmailRequest $request, TwoFactorAuthEmailService $service)
    {
        $service->send($request->input('email'));

        return response()->json([ 'message' => 'Successfully sent' ]);
    }

    public function check2faEmail(
        Check2faEmailRequest      $request,
        TwoFactorAuthEmailService $service,
        UserService               $userService,
        JWTAuth                   $auth
    ) {
        $email = $request->input('email');
        $code = $request->input('code');

        if (!$service->check($email, $code)) {
            return response()->json([ 'message' => 'Wrong code' ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $userService->findBy('email', $email);
        $token = $auth->fromUser($user);

        return response()->json([
            'message' => 'Success',
            'token' => $token
        ]);
    }

    public function check2faSms(CheckSms2faRequest $request, UserService $service, JWTAuth $auth)
    {
        $phone = $request->input('phone');

        if (!SmsTwoFactorAuthorization::check($phone, $request->input('code'))) {
            return response()->json(['message' => __('Wrong code')], Response::HTTP_BAD_REQUEST);
        }

        $user = $service->findBy('phone', $phone);

        $token = $auth->fromUser($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ])->header('Authorization', $token);
    }

    public function enableSms2fa(Enable2faSmsRequest $request)
    {
        SmsTwoFactorAuthorization::verify($request->user()->phone);

        return response('', Response::HTTP_OK);
    }

    public function resend2faSms(Enable2faSmsRequest $request)
    {
        SmsTwoFactorAuthorization::verify($request->input('phone'));

        return response('', Response::HTTP_OK);
    }

    public function confirmSms2fa(Confirm2faSmsRequest $request, UserService $service)
    {
        $isCodeCorrect = SmsTwoFactorAuthorization::check(
            $request->user()->phone,
            $request->input('code')
        );

        if (!$isCodeCorrect) {
            return response()->json([ 'message' => 'wrong code' ], Response::HTTP_BAD_REQUEST);
        }

        $service->enable2FA($request->user()->id, User::SMS_2FA_TYPE);

        return response('', Response::HTTP_OK);
    }

    public function getOtpQrCode(GetOtpQrCodeRequest $request, UserService $service)
    {
        $data = OtpTwoFactorAuthorization::generate();

        $service->update($request->user()->id, [ 'otp_secret' => $data['secret'] ]);

        return response()->json($data);
    }

    public function confirmOtp2fa(Confirm2faOtpRequest $request, UserService $service)
    {
        $isCodeCorrect = OtpTwoFactorAuthorization::check(
            $request->user()->otp_secret,
            $request->input('code')
        );

        if (!$isCodeCorrect) {
            return response()->json([ 'message' => 'wrong code' ], Response::HTTP_BAD_REQUEST);
        }

        $service->enable2FA($request->user()->id, User::OTP_2FA_TYPE);

        return response('', Response::HTTP_OK);
    }

    public function check2faOtp(Check2faOtpRequest $request, UserService $service, JWTAuth $auth)
    {
        $user = $service->find($request->input('user_id'));

        if (!OtpTwoFactorAuthorization::check($user->otp_secret, $request->input('code'))) {
            return response()->json(['message' => __('Wrong code')], Response::HTTP_BAD_REQUEST);
        }

        $token = $auth->fromUser($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ])->header('Authorization', $token);
    }
}

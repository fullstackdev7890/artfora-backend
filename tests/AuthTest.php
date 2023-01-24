<?php

namespace App\Tests;

use App\Facades\EmailTwoFactorAuthorization;
use App\Facades\OtpTwoFactorAuthorization;
use App\Facades\SmsTwoFactorAuthorization;
use App\Facades\TokenGenerator;
use App\Mails\ForgotPasswordMail;
use App\Mails\AccountConfirmationMail;
use App\Mails\TwoFactorAuthenticationMail;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AuthTest extends TestCase
{
    protected $user;
    protected $userWithoutPhone;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::find(2);
    }

    public function testRegister()
    {
        $data = $this->getJsonFixture('new_user.json');

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_OK);

        $data['email_verified_at'] = null;
        Arr::forget($data, ['password', 'confirm']);

        $this->assertDatabaseHas('users', $data);
    }

    public function testRegisterCheckVerificationToken()
    {
        TokenGenerator::shouldReceive('getRandom')->andReturn('test_token')->once();

        $data = $this->getJsonFixture('new_user.json');

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'id' => User::max('id'),
            'email_verification_token' => 'test_token'
        ]);
    }

    public function testRegisterWithRedirect()
    {
        TokenGenerator::shouldReceive('getRandom')
            ->andReturn('test_token')
            ->once();

        $data = $this->getJsonFixture('new_user.json');
        $data['redirect_after_verification'] = '/profile/promocodes';

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertMailEquals(AccountConfirmationMail::class, [
            [
                'emails' => $data['email'],
                'fixture' => 'confirm_email_with_redirect.html',
                'subject' => 'Account verification'
            ]
        ]);
    }

    public function testRegisterBySoftDeletedAccount()
    {
        $data = $this->getJsonFixture('new_user.json');
        $data['email'] = 'soft.deleted.user@email.com';

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterCheckPassword()
    {
        $data = $this->getJsonFixture('new_user.json');

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue(Hash::check($data['password'], User::find(User::max('id'))->password));
    }

    public function testRegisterCheckConfirmEmail()
    {
        TokenGenerator::shouldReceive('getRandom')
            ->andReturn('test_token')
            ->once();

        $data = $this->getJsonFixture('new_user.json');

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertMailEquals(AccountConfirmationMail::class, [
            [
                'emails' => $data['email'],
                'fixture' => 'confirm_email.html',
                'subject' => 'Account verification'
            ]
        ]);
    }

    public function testRegisterWithNotVerifiedEmail()
    {
        $data = $this->getJsonFixture('new_user.json');
        $data['email'] = 'not.verified@email.com';
        $existedUserId = User::where('email', $data['email'])->first()->id;

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_OK);

        $data['id'] = $existedUserId;
        Arr::forget($data, ['password', 'confirm']);

        $this->assertDatabaseHas('users', $data);
    }

    public function testRegisterWithConfirmedEmail()
    {
        $data = $this->getJsonFixture('new_user.json');

        $data['email'] = 'admin@email.com';

        $response = $this->json('post', '/auth/register', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testLogin()
    {
        EmailTwoFactorAuthorization::shouldReceive('send')->with('admin@example.com');

        $response = $this->json('post', '/auth/login', [
            'login' => 'admin@example.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testLoginByCapitalizedName()
    {
        EmailTwoFactorAuthorization::shouldReceive('send')->with('admin@example.com');

        $response = $this->json('post', '/auth/login', [
            'login' => 'aDmIn@example.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testLoginCheckEmail()
    {
        TokenGenerator::shouldReceive('getCode')->andReturn('123456');

        $response = $this->json('post', '/auth/login', [
            'login' => 'admin@example.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertMailEquals(TwoFactorAuthenticationMail::class, [
            [
                'emails' => 'admin@example.com',
                'fixture' => 'email_login_2fa.html',
                'subject' => 'ARTfora. 2FA code'
            ]
        ]);
    }

    public function testLoginCheckDB()
    {
        TokenGenerator::shouldReceive('getCode')->andReturn('123456');

        $response = $this->json('post', '/auth/login', [
            'login' => 'admin@example.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('two_factor_auth_emails', [
            'email' => 'admin@example.com',
            'code' => '123456'
        ]);
    }

    public function testLoginWrongCredentials()
    {
        $response = $this->json('post', '/auth/login', [
            'login' => 'admin@example.com',
            'password' => 'wrong password'
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginAsNotVerifiedAccount()
    {
        $response = $this->json('post', '/auth/login', [
            'login' => 'not.verified@email.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(Response::HTTP_NOT_ACCEPTABLE);
    }

    public function testLoginWithSms2FA()
    {
        SmsTwoFactorAuthorization::shouldReceive('verify')->andReturn('test_2fa_id')->once();

        $response = $this->json('post', '/auth/login', [
            'login' => 'user.sms.2fa@email.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals([
            'message' => 'Two factor verification required. Code has been sent',
            'type' => User::SMS_2FA_TYPE,
            'user_id' => 4
        ], $response->json());
    }

    public function testLoginWithOtp2FA()
    {
        $response = $this->json('post', '/auth/login', [
            'login' => 'user.otp.2fa@email.com',
            'password' => 'correct_password'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals([
            'message' => 'Two factor verification required. Please use authorization application',
            'type' => User::OTP_2FA_TYPE,
            'user_id' => 5
        ], $response->json());
    }

    public function testConfirmSms2FA()
    {
        SmsTwoFactorAuthorization::shouldReceive('check')
            ->with('1234567890', '123456')
            ->andReturn(true)
            ->once();

        $response = $this->json('post', '/auth/sms/confirm', [
            'code' => '123456',
            'phone' => '1234567890'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertArrayHasKey('token', $response->json());
        $this->assertNotEmpty($response->json('token'));
    }

    public function testConfirmSms2FAWrongCode()
    {
        SmsTwoFactorAuthorization::shouldReceive('check')
            ->with('1234567890', 'wrong_code')
            ->andReturn(false)
            ->once();

        $response = $this->json('post', '/auth/sms/confirm', [
            'code' => 'wrong_code',
            'phone' => '1234567890'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testConfirmOtp2FA()
    {
        OtpTwoFactorAuthorization::shouldReceive('check')
            ->with('secret', '123456')
            ->andReturn(true)
            ->once();

        $response = $this->json('post', '/auth/otp/confirm', [
            'code' => '123456',
            'user_id' => 10
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertArrayHasKey('token', $response->json());
        $this->assertNotEmpty($response->json('token'));
    }

    public function testConfirmOtp2FAWrongCode()
    {
        OtpTwoFactorAuthorization::shouldReceive('check')
            ->with('secret', 'wrong_code')
            ->andReturn(false)
            ->once();

        $response = $this->json('post', '/auth/otp/confirm', [
            'code' => 'wrong_code',
            'user_id' => 10
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testConfirmEmail()
    {
        $response = $this->json('post', '/auth/email/confirm', [
            'token' => 'correct_confirmation_code'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertArrayHasKey('token', $response->json());
        $this->assertNotEmpty($response->json('token'));
    }

    public function testConfirmEmailNotFound()
    {
        $response = $this->json('post', '/auth/email/confirm', [
            'token' => 'not_existed_code'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testConfirmEmailWithOldToken()
    {
        $response = $this->json('post', '/auth/email/confirm', [
            'token' => 'old_token'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testConfirmEmailCheckDB()
    {
        $response = $this->json('post', '/auth/email/confirm', [
            'token' => 'correct_confirmation_code'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'id' => 3,
            'email_verification_token' => null,
            'email_verification_token_sent_at' => null,
            'email_verified_at' => Carbon::now()
        ]);
    }

    public function testConfirmEmailWrongConfirmationCode()
    {
        $response = $this->json('post', '/auth/email/confirm', [
            'token' => 'wrong_confirmation_code'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRefreshToken()
    {
        $response = $this->actingAs($this->user)->json('get', '/auth/refresh');

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNotEmpty(
            $response->headers->get('authorization')
        );

        $auth = $response->headers->get('authorization');

        $explodedHeader = explode(' ', $auth);

        $this->assertNotEquals($this->jwt, last($explodedHeader));
    }

    public function testForgotPassword()
    {
        TokenGenerator::shouldReceive('getRandom')
            ->andReturn('some_token')
            ->once();

        $response = $this->json('post', '/auth/forgot-password', [
            'login' => 'correct@email.com'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('password_resets', [
            'email' => 'correct@email.com',
            'token' => 'some_token',
            'created_at' => Carbon::now()
        ]);

        $this->assertMailEquals(ForgotPasswordMail::class, [
            [
                'emails' => 'correct@email.com',
                'fixture' => 'forgot_password_email.html'
            ]
        ]);
    }

    public function testForgotPasswordUserDoesNotExists()
    {
        $response = $this->json('post', '/auth/forgot-password', [
            'login' => 'not_exists@example.com'
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testForgotPasswordEmailNotConfirmed()
    {
        $response = $this->json('post', '/auth/forgot-password', [
            'login' => 'not.confirmed@email.com'
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testRestorePassword()
    {
        $response = $this->json('post', '/auth/restore-password', [
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
            'token' => 'restore_token',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('users', [
            'email' => 'restore@email.com',
            'password' => 'old_password'
        ]);

        $this->assertDatabaseMissing('password_resets', [
            'token' => 'restore_token'
        ]);
    }

    public function testRestorePasswordCheckPassword()
    {
        $response = $this->json('post', '/auth/restore-password', [
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
            'token' => 'restore_token',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertTrue(Hash::check('new_password', User::find(6)->password));
    }

    public function testRestorePasswordWrongToken()
    {
        $response = $this->json('post', '/auth/restore-password', [
            'password' => 'new_password',
            'token' => 'incorrect_token',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCheckRestoreToken()
    {
        $response = $this->json('post', '/auth/token/check', [
            'token' => 'restore_token',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testCheckRestoreWrongToken()
    {
        $response = $this->json('post', '/auth/token/check', [
            'token' => 'wrong_token',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testClearPasswordResets()
    {
        $this->artisan('password_resets:clear');

        $this->assertDatabaseHas('password_resets', [
            'token' => 'restore_token'
        ]);

        $this->assertDatabaseMissing('password_resets', [
            'token' => 'old_token'
        ]);
    }

    public function testSendSms()
    {
        SmsTwoFactorAuthorization::shouldReceive('verify')->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/sms/send');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testSendSmsAsUserWithoutPhone()
    {
        $response = $this->actingAs($this->userWithoutPhone)->json('post', '/auth/sms/send');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testSendSmsNoAuth()
    {
        $response = $this->json('post', '/auth/sms/send');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetOtpQrCode()
    {
        OtpTwoFactorAuthorization::shouldReceive('generate')->andReturn([
            'secret' => 'secret',
            'qr_code' => 'test-url'
        ])->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/otp/generate');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals([
            'secret' => 'secret',
            'qr_code' => 'test-url'
        ], $response->json());

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'otp_secret' => 'secret'
        ]);
    }

    public function testGetOtpQrCodeNoAuth()
    {
        $response = $this->json('post', '/auth/otp/generate');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testEnableSmsCode()
    {
        SmsTwoFactorAuthorization::shouldReceive('check')->andReturn(true)->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/sms/check', [
            'code' => 'right_code'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'is_2fa_enabled' => true,
            '2fa_type' => User::SMS_2FA_TYPE
        ]);
    }

    public function testEnableSmsCodeCheckPromocodeCreation()
    {
        SmsTwoFactorAuthorization::shouldReceive('check')->andReturn(true)->once();
        TokenGenerator::shouldReceive('getRandom')
            ->andReturn('test_promo')
            ->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/sms/check', [
            'code' => 'right_code'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('promocodes', [
            'user_id' => $this->user->id,
            'code' => 'test_promo'
        ]);
    }

    public function testEnableSms2faWrongCode()
    {
        SmsTwoFactorAuthorization::shouldReceive('check')->andReturn(false)->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/sms/check', [
            'code' => 'right_code'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testEnableSms2FACodeWithoutPhone()
    {
        $response = $this->actingAs($this->userWithoutPhone)->json('post', '/auth/sms/check', [
            'code' => 'right_code'
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testEnableSms2FACodeNoAuth()
    {
        $response = $this->json('post', '/auth/sms/check');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testEnableOtpCode()
    {
        OtpTwoFactorAuthorization::shouldReceive('check')->andReturn(true)->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/otp/check', [
            'code' => 'right_code'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'is_2fa_enabled' => true,
            '2fa_type' => User::OTP_2FA_TYPE
        ]);
    }

    public function testEnableOtpCodeCheckPromocodeCreation()
    {
        OtpTwoFactorAuthorization::shouldReceive('check')->andReturn(true)->once();
        TokenGenerator::shouldReceive('getRandom')
            ->andReturn('test_promo')
            ->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/otp/check', [
            'code' => 'right_code'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('promocodes', [
            'id' => $this->user->id,
            'code' => 'test_promo'
        ]);
    }

    public function testEnableOtp2faWrongCode()
    {
        OtpTwoFactorAuthorization::shouldReceive('check')->andReturn(false)->once();

        $response = $this->actingAs($this->user)->json('post', '/auth/otp/check', [
            'code' => 'right_code'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testEnableOtp2FACodeNoAuth()
    {
        $response = $this->json('post', '/auth/otp/check');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}

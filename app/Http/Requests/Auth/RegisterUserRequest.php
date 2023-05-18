<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use App\Services\UserService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Carbon\Carbon;

class RegisterUserRequest extends Request
{
    public function rules(): array
    {
        $user = app(UserService::class)
            ->findBy('email', $this->input('email'));
        $current = Carbon::now();
        if ($user && !$user['email_verified_at'] && $current > (new Carbon($user['email_verification_token_sent_at']))->addMinutes(30)) {
            app(UserService::class)->force()->delete($user['id']);
        }

        return [
            'username' => 'required|string|unique:users,username',
            'tagname' => 'required|string|unique:users,tagname',
            'email' => 'required|string|email',
            'password' => 'required|string|same:confirm',
            'confirm' => 'required|string',
            'redirect_after_verification'  => 'string'
        ];
    }

    public function validateResolved()
    {
        parent::validateResolved();

        $user = app(UserService::class)
            ->withTrashed()
            ->findBy('email', $this->input('email'));

        if (empty($user)) {
            return;
        }

        if (empty($user['email_verified_at'])) {
            return;
        }

        throw new BadRequestHttpException('Email has already been taken');
    }
}

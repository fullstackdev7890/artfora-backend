<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use App\Services\UserService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RegisterUserRequest extends Request
{
    public function rules(): array
    {
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

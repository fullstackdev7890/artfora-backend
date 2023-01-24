<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class RegisterUserRequest extends Request
{
    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'tagname' => 'required|string|unique:users,tagname',
            'email' => 'required|string|email',
            'password' => 'required|string|same:confirm',
            'confirm' => 'required|string'
        ];
    }
}

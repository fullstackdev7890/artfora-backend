<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'login' => 'string|email|required',
            'password' => 'required',
        ];
    }
}

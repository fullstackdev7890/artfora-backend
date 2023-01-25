<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class CheckRestorePasswordTokenRequest extends Request
{
    public function rules(): array
    {
        return [
            'token' => 'required|string|exists:users,reset_password_hash'
        ];
    }
}

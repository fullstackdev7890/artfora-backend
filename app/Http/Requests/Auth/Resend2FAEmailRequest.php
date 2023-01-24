<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class Resend2FAEmailRequest extends FormRequest
{
    public function authorize()
    {
        return !empty($this->user()->email_verified_at);
    }

    public function rules()
    {
        return [
            'email' => 'string|email|required|exists:users,email'
        ];
    }
}

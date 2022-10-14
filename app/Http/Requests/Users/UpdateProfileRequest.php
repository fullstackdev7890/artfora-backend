<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Request;

class UpdateProfileRequest extends Request
{
    public function rules(): array
    {
        $userId = $this->user() ? $this->user()->id : '';

        return [
            'password' => 'string|same:confirm',
            'confirm' => 'string',
            'email' => "string|email|unique:users,email,{$userId}",
            'name' => 'string',
        ];
    }
}
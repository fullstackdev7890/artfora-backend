<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Request;

class UpdateProfileRequest extends Request
{
    public function rules(): array
    {
        $userId = $this->user() ? $this->user()->id : '';

        return [
            'username' => "string|unique:users,{$userId}",
            'tagname' => 'string',
            'email' => "email|unique:users,email,{$userId}",
            'password' => 'string|same:confirm',
            'confirm' => 'string',
            'role_id' => 'integer|exists:roles,id',
            'description' => 'string|nullable',
            'country' => 'string|nullable',
            'external_link' => 'string|nullable',
            'background_image_id' => 'integer|exists:media,id|nullable',
            'avatar_image_id' => 'integer|exists:media,id|nullable',
            'data' => 'array',
            'data.media_filters' => 'array'
        ];
    }
}
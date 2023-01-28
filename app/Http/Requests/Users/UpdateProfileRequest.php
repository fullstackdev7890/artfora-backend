<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Request;
use App\Models\Product;

class UpdateProfileRequest extends Request
{
    public function rules(): array
    {
        $userId = $this->user() ? $this->user()->id : '';
        $visibilityLevels = join(',', Product::VISIBILITY_LEVELS);

        return [
            'username' => "string|unique:users,username,{$userId}",
            'tagname' => "string|unique:users,tagname,{$userId}",
            'email' => "email|unique:users,email,{$userId}",
            'password' => 'string|same:confirm',
            'confirm' => 'string',
            'role_id' => 'integer|exists:roles,id',
            'description' => 'string|nullable',
            'country' => 'string|nullable',
            'external_link' => 'string|nullable',
            'background_image_id' => 'integer|exists:media,id|nullable',
            'avatar_image_id' => 'integer|exists:media,id|nullable',
            'product_visibility_level' => "integer|in:{$visibilityLevels}",
            'data' => 'array',
            'data.media_filters' => 'array'
        ];
    }
}
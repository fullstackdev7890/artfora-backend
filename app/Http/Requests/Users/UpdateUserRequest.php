<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Request;
use App\Models\Role;
use App\Services\UserService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateUserRequest extends Request
{
    public function authorize(): bool
    {
        return ($this->user()->role_id === Role::ADMIN) || ($this->user()->id === $this->route('id'));
    }

    public function rules(): array
    {
        $userId = $this->route('id');

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
            'data' => 'array',
            'data.media_filters' => 'array'
        ];
    }

    public function validateResolved()
    {
        parent::validateResolved();

        $service = app(UserService::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'User']));
        }

        if ($this->has('role_id') && $this->user()->role_id !== Role::ADMIN) {
            throw new AccessDeniedHttpException(__('validation.exceptions.not_found', ['entity' => 'User']));
        }
    }
}

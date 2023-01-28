<?php

namespace App\Http\Requests\ContactUs;

use App\Http\Requests\Request;
use App\Services\UserService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommissionRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'string|required',
            'email' => 'string|email|required',
            'message' => 'string|required'
        ];
    }

    public function validateResolved()
    {
        $service = app(UserService::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'Product']));
        }

        parent::validateResolved();
    }
}

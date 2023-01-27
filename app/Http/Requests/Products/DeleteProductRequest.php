<?php

namespace App\Http\Requests\Products;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\ProductService;
use App\Http\Requests\Request;

class DeleteProductRequest extends Request
{
    public function rules(): array
    {
        return [];
    }

    public function validateResolved()
    {
        parent::validateResolved();

        $service = app(ProductService::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'Product']));
        }
    }
}

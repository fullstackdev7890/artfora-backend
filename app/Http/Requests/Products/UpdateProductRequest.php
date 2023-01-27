<?php

namespace App\Http\Requests\Products;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\ProductService;
use App\Http\Requests\Request;

class UpdateProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'price' => 'integer',
            'user_id' => 'integer|exists:users,id|required',
            'category_id' => 'integer|exists:categories,id|required',
            'weight' => 'numeric',
            'author' => 'string',
            'title' => 'string',
            'slug' => 'string',
            'description' => 'string',
            'status' => 'string',
            'tags' => 'string',
            'visibility_level' => 'string',
            'is_ai_safe' => 'boolean',
        ];
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

<?php

namespace App\Http\Requests\Products;

use App\Models\Product;
use App\Models\Role;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\ProductService;
use App\Http\Requests\Request;

class UpdateProductRequest extends Request
{
    protected Product | null $product;

    public function authorize(): bool
    {
        if ($this->user()->role_id === Role::ADMIN) {
            return true;
        }

        if ($this->user()->id !== $this->product->user_id) {
            return false;
        }

        return !$this->input('status') || ($this->input('status') === $this->product->status);
    }

    public function rules(): array
    {
        $visibilityLevels = join(',', Product::VISIBILITY_LEVELS);

        return [
            'width' => 'integer',
            'height' => 'integer',
            'price' => 'integer',
            'category_id' => 'integer|exists:categories,id',
            'weight' => 'numeric',
            'author' => 'string',
            'title' => 'string',
            'slug' => 'string',
            'description' => 'string',
            'status' => 'string',
            'tags' => 'string',
            'visibility_level' => "integer|in:{$visibilityLevels}",
            'is_ai_safe' => 'boolean',
            'media' => 'array',
            'media.*' => 'integer|exists:media,id'
        ];
    }

    public function validateResolved()
    {
        $service = app(ProductService::class);
        $this->product = $service->find($this->route('id'));

        if (empty($this->product)) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => 'Product']));
        }

        parent::validateResolved();
    }
}

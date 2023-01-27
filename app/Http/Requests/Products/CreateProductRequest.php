<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\Request;

class CreateProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'price' => 'integer|required',
            'user_id' => 'integer|exists:users,id|required',
            'category_id' => 'integer|exists:categories,id|required',
            'weight' => 'numeric',
            'author' => 'string',
            'title' => 'string|required',
            'slug' => 'string|required',
            'description' => 'string|required',
            'status' => 'string|required',
            'tags' => 'string|required',
            'visibility_level' => 'string|required',
            'is_ai_safe' => 'boolean',
        ];
    }
}

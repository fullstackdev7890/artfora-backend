<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\Request;

class SearchProductsRequest extends Request
{
    public function rules(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'page' => 'integer',
            'per_page' => 'integer',
            'all' => 'integer',
            'weight' => 'numeric',
            'order_by' => 'string',
            'desc' => 'boolean',
            'with' => 'array',
            'query' => 'string|nullable',
            'with.*' => 'string|required',
        ];
    }
}

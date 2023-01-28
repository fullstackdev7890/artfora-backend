<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\Request;
use App\Models\Product;

class SearchProductsRequest extends Request
{
    public function rules(): array
    {
        $statuses = join(',', Product::STATUSES);

        return [
            'page' => 'integer',
            'per_page' => 'integer',
            'all' => 'integer',
            'user_id' => 'integer|exists:users,id',
            'category_id' => 'integer|exists:categories,id',
            'query' => 'string|nullable',
            'status' => "string|in:{$statuses}",
            'order_by' => 'string|in:created_at,random',
            'desc' => 'boolean',
            'with' => 'array',
            'with.*' => 'string|required',
        ];
    }
}

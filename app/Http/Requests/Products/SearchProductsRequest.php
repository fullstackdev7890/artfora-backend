<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\Request;
use App\Models\Product;

/**
 * @description
 * If a regular user(not admin) tries to search by products he will see only Approved products.
 * If they need to see their own products with other statuses they need to mention their `user_id` in
 * the request.
 * Also, requesting parent category cause requesting child categories too.
 */
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
            'visibility_level_from' => 'integer|min:' . Product::COMMON_VISIBILITY_LEVEL,
            'visibility_level_to' => 'integer|max:' . Product::PORNO_VISIBILITY_LEVEL,
        ];
    }
}

<?php

namespace App\Http\Requests\Categories;

use App\Http\Requests\Request;

class CreateCategoryRequest extends Request
{
    public function rules(): array
    {
        return [
            'title' => 'string|required',
            'parent_id' => 'integer|nullable|exists:categories,id'
        ];
    }
}

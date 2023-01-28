<?php

namespace App\Repositories;

use App\Models\Category;

/**
 * @property Category $model
 */
class CategoryRepository extends Repository
{
    public function __construct()
    {
        $this->setModel(Category::class);
    }
}

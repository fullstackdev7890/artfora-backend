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

    public function filterOnlyParents(): self
    {
        if (isset($this->filter['only_parents'])) {
            $this->query->whereNull('parent_id');
        }

        return $this;
    }
}

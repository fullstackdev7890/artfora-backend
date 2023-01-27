<?php

namespace App\Repositories;

use App\Models\Product;

/**
 * @property Product $model
 */
class ProductRepository extends Repository
{
    public function __construct()
    {
        $this->setModel(Product::class);
    }
}

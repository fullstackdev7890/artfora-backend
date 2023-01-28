<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Product $model
 */
class ProductRepository extends Repository
{
    public function __construct()
    {
        $this->setModel(Product::class);
    }

    protected function afterCreateHook(?Model $entity, array $data)
    {
        if (isset($data['media'])) {
            $entity->media()->sync($data['media']);
        }
    }
}

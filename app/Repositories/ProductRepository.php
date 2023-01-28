<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * @property Product $model
 */
class ProductRepository extends Repository
{
    public function __construct()
    {
        $this->setModel(Product::class);
    }

    public function filterByStatus(): self
    {
        $user = Auth::user();

        if (empty($user)) {
            $this->query->where('status', Product::APPROVED_STATUS);
        }

        if ($user->role_id === Role::ADMIN) {
            return $this;
        }

        if (!isset($this->filter['user_id']) || ($this->filter['user_id'] !== $user->id)) {
            $this->query->where('status', Product::APPROVED_STATUS);
        }

        return $this;
    }

    public function filterByCategory(): self
    {
        if (isset($this->filter['category_id'])) {
            $this->query->where(function (Builder $subQuery) {
                $subQuery
                    ->where('category_id', $this->filter['category_id'])
                    ->orWhereHas('category', function ($categoryQuery) {
                        $categoryQuery->where('parent_id', $this->filter['category_id']);
                    });
            });
        }

        return $this;
    }

    protected function afterCreateHook(?Model $entity, array $data)
    {
        if (isset($data['media'])) {
            $entity->media()->sync($data['media']);
        }
    }

    protected function afterUpdateHook(?Model $entity, array $data)
    {
        if (isset($data['media'])) {
            $entity->media()->sync($data['media']);
        }
    }
}

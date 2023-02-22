<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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

            return $this;
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
        if (isset($this->filter['categories'])) {
            $this->query->where(function (Builder $subQuery) {
                $subQuery
                    ->whereIn('category_id', $this->filter['categories'])
                    ->orWhereHas('category', function ($categoryQuery) {
                        $categoryQuery->whereIn('parent_id', $this->filter['categories']);
                    });
            });
        }

        return $this;
    }

    public function filterByVisibilityLevel(): self
    {
        $user = Auth::user();

        if (empty($user)) {
            $this->query->where('visibility_level', Product::COMMON_VISIBILITY_LEVEL);

            return $this;
        }

        if ($user->role_id === Role::ADMIN) {
            return $this;
        }

        if ($user->id === Arr::get($this->filter, 'user_id')) {
            return $this;
        }

        $this->query->where('visibility_level', '<=', $user->product_visibility_level);

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

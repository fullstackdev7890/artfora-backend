<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Artel\Support\Services\EntityService;
use App\Repositories\ProductRepository;

/**
 * @mixin ProductRepository
 * @property ProductRepository $repository
 */
class ProductService extends EntityService
{
    public function __construct()
    {
        $this->setRepository(ProductRepository::class);
    }

    public function search($filters)
    {
        return $this
            ->with(Arr::get($filters, 'with', []))
            ->withCount(Arr::get($filters, 'with_count', []))
            ->searchQuery($filters)
            ->filterBy('width')
            ->filterBy('height')
            ->filterBy('user_id')
            ->filterBy('category_id')
            ->filterBy('price')
            ->filterBy('is_ai_safe')
            ->filterByQuery(['author', 'title', 'slug', 'description', 'status', 'tags', 'visibility_level'])
            ->getSearchResults();
    }
}

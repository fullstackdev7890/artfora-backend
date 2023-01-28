<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Artel\Support\Services\EntityService;
use App\Repositories\ProductRepository;
use Illuminate\Support\Str;

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

    public function create($data)
    {
        $data['slug'] = Str::slug($data['title']);

        return $this->repository
            ->with(['media'])
            ->create($data);
    }

    public function update($where, $data)
    {
        return $this->repository
            ->with(['media'])
            ->update($where, $data);
    }

    public function search($filters)
    {
        return $this
            ->with(Arr::get($filters, 'with', []))
            ->withCount(Arr::get($filters, 'with_count', []))
            ->searchQuery($filters)
            ->filterBy('user_id')
            ->filterBy('status')
            ->filterBy('is_ai_safe')
            ->filterByQuery(['author', 'title', 'description', 'tags'])
            ->filterByStatus()
            ->filterByCategory()
            ->getSearchResults();
    }
}

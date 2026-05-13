<?php

namespace App\Repositories\Decorators;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class CachingCategoryRepository implements CategoryRepositoryInterface
{
    protected $repository;
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(?string $searchTerm = '')
    {
        $key = 'categories.all.q' . md5($searchTerm ?? '');
        return Cache::tags(['categories'])->rememberForever($key, function () use ($searchTerm) {
            return $this->repository->getAll($searchTerm)->toArray();
        });
    }

    public function getById(string $id)
    {
        return Cache::tags(['categories'])->rememberForever("categories.{$id}", function () use ($id) {
            return $this->repository->getById($id);
        });
    }

    public function create(array $attributes)
    {
        Cache::tags(['categories'])->flush();
        return $this->repository->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        Cache::tags(['categories'])->flush();
        return $this->repository->update($id, $attributes);
    }

    public function delete(string $id)
    {
        Cache::tags(['categories'])->flush();
        return $this->repository->delete($id);
    }
}

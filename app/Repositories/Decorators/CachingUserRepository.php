<?php

namespace App\Repositories\Decorators;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class CachingUserRepository implements UserRepositoryInterface
{
    protected $repository;
    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(?string $searchTerm = '', ?int $page = null, ?int $limit = null)
    {
        $key = 'users.all.q' . md5($searchTerm ?? '') . ".p{$page}.l{$limit}";
        return Cache::tags(['users'])->remember($key, 3600, function () use ($searchTerm, $page, $limit) {
            return $this->repository->getAll($searchTerm, $page, $limit)->toArray();
        });
    }

    public function getById(string $id)
    {
        return Cache::tags(['users'])->remember("users.{$id}", 3600, function () use ($id) {
            return $this->repository->getById($id);
        });
    }

    public function create(array $attributes)
    {
        Cache::tags(['users'])->flush();
        return $this->repository->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        Cache::tags(['users'])->flush();
        return $this->repository->update($id, $attributes);
    }

    public function delete(string $id)
    {
        Cache::tags(['users'])->flush();
        return $this->repository->delete($id);
    }
}

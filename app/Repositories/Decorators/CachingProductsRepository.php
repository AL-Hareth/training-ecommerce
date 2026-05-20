<?php

namespace App\Repositories\Decorators;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class CachingProductsRepository implements ProductRepositoryInterface
{
    protected $repository;
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(?string $searchTerm = '', ?int $page = null, ?int $limit = null, array $attributeValueIds = [])
    {
        $key = 'products.all.q' . md5($searchTerm ?? '') . ".p{$page}.l{$limit}.a" . md5(json_encode($attributeValueIds));
        return Cache::tags(['products'])->remember($key, 3600, function () use ($searchTerm, $page, $limit, $attributeValueIds) {
            return $this->repository->getAll($searchTerm, $page, $limit, $attributeValueIds)->toArray();
        });
    }

    public function getByVendorId(string $vendorId, ?string $searchTerm = '')
    {
        return $this->repository->getByVendorId($vendorId, $searchTerm);
    }

    public function getById(string $id)
    {
        return $this->repository->getById($id);
    }

    public function getByIdLocked(string $id) {
        return $this->repository->getByIdLocked($id);
    }

    public function getVariantByIdLocked(string $id) {
        return $this->repository->getVariantByIdLocked($id);
    }

    public function create(array $attributes)
    {
        Cache::tags(['products'])->flush();
        return $this->repository->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        Cache::tags(['products'])->flush();
        return $this->repository->update($id, $attributes);
    }

    public function updateVariantStock(string $variantId, int $quantity)
    {
        Cache::tags(['products'])->flush();
        return $this->repository->updateVariantStock($variantId, $quantity);
    }

    public function delete(string $id)
    {
        Cache::tags(['products'])->flush();
        return $this->repository->delete($id);
    }

    public function countByAttributeValueId(string $attributeValueId): int
    {
        return $this->repository->countByAttributeValueId($attributeValueId);
    }
}

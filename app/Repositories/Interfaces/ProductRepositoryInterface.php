<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface {
    public function getAll(?string $searchTerm = '', int|null $page = null, int|null $limit = null, array $attributeValueIds = []);
    public function getByVendorId(string $vendorId, ?string $searchTerm = '');
    public function getById(string $id);
    public function getByIdLocked(string $id);
    public function create(array $attributes);
    public function update(string $id, array $attributes);
    public function delete(string $id);
    public function countByAttributeValueId(string $attributeValueId): int;
}

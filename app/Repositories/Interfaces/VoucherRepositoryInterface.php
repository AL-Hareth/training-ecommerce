<?php

namespace App\Repositories\Interfaces;

interface VoucherRepositoryInterface
{
    public function getAllForVendor(string $vendorId);
    public function createForVendor(string $vendorId, array $data);
    public function findByIdAndVendor(string $id, string $vendorId);
    public function update(string $id, array $data);
    public function delete(string $id);
    public function findValidByCode(string $code);
    public function getByIds(array $ids);
    public function findById(string $id);
    public function incrementUsage(string $id);
    public function decrementUsage(string $id);
}

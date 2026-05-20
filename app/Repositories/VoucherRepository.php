<?php

namespace App\Repositories;

use App\Models\Voucher;
use App\Repositories\Interfaces\VoucherRepositoryInterface;

class VoucherRepository implements VoucherRepositoryInterface
{
    protected $model;

    public function __construct(Voucher $voucher)
    {
        $this->model = $voucher;
    }

    public function getAllForVendor(string $vendorId)
    {
        return $this->model->where('vendor_id', $vendorId)->latest()->get();
    }

    public function createForVendor(string $vendorId, array $data)
    {
        $data['vendor_id'] = $vendorId;
        return $this->model->create($data);
    }

    public function findByIdAndVendor(string $id, string $vendorId)
    {
        return $this->model->where('vendor_id', $vendorId)->findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $voucher = $this->model->findOrFail($id);
        $voucher->update($data);
        return $voucher;
    }

    public function delete(string $id)
    {
        $voucher = $this->model->findOrFail($id);
        return $voucher->delete();
    }

    public function findValidByCode(string $code)
    {
        return $this->model->where('code', strtoupper($code))
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();
    }

    public function getByIds(array $ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    public function findById(string $id)
    {
        return $this->model->find($id);
    }

    public function incrementUsage(string $id)
    {
        return $this->model->where('id', $id)->increment('used_count');
    }

    public function decrementUsage(string $id)
    {
        return $this->model->where('id', $id)
            ->where('used_count', '>', 0)
            ->decrement('used_count');
    }
}

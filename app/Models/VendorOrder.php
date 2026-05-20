<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VendorOrder extends Model
{
    use HasUuids;
    protected $table = 'vendor_order';
    protected $guarded = [];

    public function vendor() {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function voucher() {
        return $this->belongsTo(Voucher::class);
    }
}

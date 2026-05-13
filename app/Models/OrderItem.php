<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasUuids;
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'ordering_price'
    ];

    public function vendorOrder() {
        return $this->belongsTo(VendorOrder::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}

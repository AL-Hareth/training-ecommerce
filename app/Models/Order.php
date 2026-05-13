<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Order extends Model
{
    use HasUuids, Searchable;

    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function vendorOrders() {
        return $this->hasMany(VendorOrder::class);
    }

    public function searchableAs(): string
    {
        return 'orders';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('user');

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'customer_name' => $this->user?->name,
            'customer_email' => $this->user?->email,
            'total_price' => (float) $this->total_price,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'shipping_address' => $this->shipping_address,
            'shipping_phone' => $this->shipping_phone,
            'created_at' => $this->created_at?->timestamp,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function items() {
        return $this->hasMany(CartItem::class);
    }
}

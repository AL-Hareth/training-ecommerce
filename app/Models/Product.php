<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\BorderType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['name', 'description', 'vendor_id', 'category_id', 'price', 'stock', 'image', 'discount_type', 'discount_value', 'discount_expiration'])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUuids, InteractsWithMedia, Searchable;

    protected $with = ['media'];

    protected $appends = ['discounted_price'];

    protected $casts = [
        'discount_expiration' => 'datetime',
        'price' => 'float',
        'discount_value' => 'float',
        'stock' => 'integer',
    ];

    public function getDiscountedPriceAttribute()
    {
        if (!$this->discount_type || !$this->discount_value) {
            return (float) $this->price;
        }

        if ($this->discount_expiration && $this->discount_expiration->isPast()) {
            return (float) $this->price;
        }

        if ($this->discount_type === 'percentage') {
            return max(0, (float) $this->price - ((float) $this->price * ((float) $this->discount_value / 100)));
        }

        if ($this->discount_type === 'fixed') {
            return max(0, (float) $this->price - (float) $this->discount_value);
        }

        return (float) $this->price;
    }

    public function vendor() {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->sharpen(10)
            ->nonQueued();
    }

    public function searchableAs(): string
    {
        return 'products';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'vendor_id' => $this->vendor_id,
            'category_id' => $this->category_id,
            'price' => (float) $this->price,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'discount_expiration' => $this->discount_expiration?->timestamp,
            'discounted_price' => $this->discounted_price,
            'stock' => (int) $this->stock,
            'created_at' => $this->created_at->timestamp,
            'attribute_values' => $this->relationLoaded('attributeValues')
                ? $this->attributeValues->pluck('id')->toArray()
                : $this->attributeValues()->pluck('id')->toArray(),
        ];
    }
}

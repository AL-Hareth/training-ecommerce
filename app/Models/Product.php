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

#[Fillable(['name', 'description', 'vendor_id', 'category_id', 'price', 'stock', 'image'])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUuids, InteractsWithMedia, Searchable;

    protected $with = ['media'];

    public function vendor() {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
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
            'stock' => (int) $this->stock,
            'created_at' => $this->created_at->timestamp,
            'attribute_values' => $this->relationLoaded('attributeValues')
                ? $this->attributeValues->pluck('id')->toArray()
                : $this->attributeValues()->pluck('id')->toArray(),
        ];
    }
}

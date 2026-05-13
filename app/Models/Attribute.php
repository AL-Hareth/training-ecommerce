<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug'];

    /**
     * Auto-generate slug from name if not provided.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}

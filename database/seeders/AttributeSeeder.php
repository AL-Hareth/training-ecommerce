<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Color' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'],
            'Size'  => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'Brand' => ['Nike', 'Adidas', 'Puma', 'Reebok', 'Under Armour'],
        ];

        foreach ($attributes as $name => $values) {
            /** @var Attribute $attr */
            $attr = Attribute::firstOrCreate(
                ['name' => $name],
                ['name' => $name, 'slug' => Str::slug($name)]
            );

            foreach ($values as $value) {
                AttributeValue::firstOrCreate(
                    ['attribute_id' => $attr->id, 'value' => $value],
                    ['attribute_id' => $attr->id, 'value' => $value],
                );
            }
        }
    }
}

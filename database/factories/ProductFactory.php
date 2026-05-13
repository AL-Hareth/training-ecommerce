<?php

namespace Database\Factories;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 100),
            'category_id' => Category::factory(),
            'image' => fake()->imageUrl(),
            'vendor_id' => User::where('role', 'vendor')->orWhere('role', 'admin')->inRandomOrder()->first()->id,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $attributeValues = AttributeValue::inRandomOrder()->take(3)->pluck('id');
            $product->attributeValues()->attach($attributeValues);
        });
    }
}

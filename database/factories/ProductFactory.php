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
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
            'category_id' => Category::factory(),
            'image' => $this->faker->imageUrl(),
            'vendor_id' => User::where('role', 'vendor')->orWhere('role', 'admin')->first()?->id ?? User::factory()->create(['role' => 'vendor'])->id,
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

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\ProcessCheckoutAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessCheckoutActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\AttributeSeeder::class);
    }

    public function test_it_deducts_stock_at_variant_level()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'price' => 120,
            'stock' => 5,
            'attributes' => ['color' => 'red']
        ]);

        $data = [
            'user_id' => $user->id,
            'total_price' => 120,
            'payment_method' => 'cod',
            'shipping_address' => '123 Test St',
            'shipping_phone' => '1234567890',
            'vendors' => [
                $vendor->id => [
                    'subtotal' => 120,
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'price' => 120,
                            'quantity' => 2,
                        ]
                    ]
                ]
            ]
        ];

        $action = app(ProcessCheckoutAction::class);
        $action->execute($data);

        $this->assertEquals(3, $product->fresh()->stock); // Product stock now reflects the variant's stock
        $this->assertEquals(3, $variant->fresh()->stock); // Variant stock should be deducted
        
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2
        ]);
    }

    public function test_it_deducts_stock_at_product_level_when_no_variant_is_provided()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 100
        ]);

        $data = [
            'user_id' => $user->id,
            'total_price' => 100,
            'payment_method' => 'cod',
            'shipping_address' => '123 Test St',
            'shipping_phone' => '1234567890',
            'vendors' => [
                $vendor->id => [
                    'subtotal' => 100,
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'price' => 100,
                            'quantity' => 3,
                        ]
                    ]
                ]
            ]
        ];

        $action = app(ProcessCheckoutAction::class);
        $action->execute($data);

        $this->assertEquals(7, $product->fresh()->stock); // Product stock should be deducted
        
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 3
        ]);
    }

    public function test_it_throws_exception_if_variant_stock_is_insufficient()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'price' => 120,
            'stock' => 1,
            'attributes' => ['color' => 'red']
        ]);

        $data = [
            'user_id' => $user->id,
            'total_price' => 120,
            'payment_method' => 'cod',
            'shipping_address' => '123 Test St',
            'shipping_phone' => '1234567890',
            'vendors' => [
                $vendor->id => [
                    'subtotal' => 120,
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'price' => 120,
                            'quantity' => 2,
                        ]
                    ]
                ]
            ]
        ];

        $action = app(ProcessCheckoutAction::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Product Variant has insufficient stock");

        $action->execute($data);
    }

    public function test_it_recalculates_price_if_discount_is_expired()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::factory()->create();

        // Product with 50% discount but expired 1 hour ago
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 200,
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'discount_expiration' => now()->subHour()
        ]);

        $data = [
            'user_id' => $user->id,
            'total_price' => 100, // Frontend thinks it's 100
            'payment_method' => 'cod',
            'shipping_address' => '123 Test St',
            'shipping_phone' => '1234567890',
            'vendors' => [
                $vendor->id => [
                    'subtotal' => 100,
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'price' => 100,
                            'quantity' => 1,
                        ]
                    ]
                ]
            ]
        ];

        $action = app(ProcessCheckoutAction::class);
        $order = $action->execute($data);

        // Order total should be 200, not 100
        $this->assertEquals(200, $order->total_price);
        
        $orderItem = $order->vendorOrders->first()->items->first();
        $this->assertEquals(200, $orderItem->ordering_price);
    }
}

<?php

namespace App\Services;

use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProcessCheckoutAction
{
    public function __construct(
        protected OrderRepositoryInterface   $orderRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CartRepositoryInterface $cartRepository
    ) {}

    public function execute(array $data)
    {
        return DB::transaction(function () use ($data) {

            // 1. Validate Stock (Using your excellent lock method!)
            $validatedProducts = $this->validateAndLockInventory($data['vendors']);

            // 2. Create Parent Order
            $order = $this->orderRepository->create([
                'user_id'          => $data['user_id'],
                'total_price'      => $data['total_price'],
                'payment_method'   => $data['payment_method'],
                'shipping_address' => $data['shipping_address'],
                'shipping_phone'   => $data['shipping_phone'],
                'status'           => 'pending',
            ]);

            // 3. Process Vendors & Items
            foreach ($data['vendors'] as $vendorId => $vendorData) {

                // Create Sub-Order via repository
                $vendorOrder = $this->orderRepository->createVendorOrder($order, [
                    'vendor_id' => $vendorId,
                    'subtotal'  => $vendorData['subtotal'],
                    'status'    => 'pending',
                ]);

                $itemsToInsert = [];
                foreach ($vendorData['items'] as $item) {
                    $itemsToInsert[] = [
                        'product_id'     => $item['product_id'],
                        'ordering_price' => $item['price'],
                        'quantity'       => $item['quantity'],
                    ];

                    // 4. Deduct Stock
                    $product = $validatedProducts[$item['product_id']];
                    $this->productRepository->update($item['product_id'], [
                        'stock' => $product->stock - $item['quantity']
                    ]);
                }

                // Insert Items via repository
                $this->orderRepository->insertVendorOrderItems($vendorOrder, $itemsToInsert);
            }

            $this->cartRepository->clearCart($order->user_id);

            return $order->load('vendorOrders.items');
        });
    }

    private function validateAndLockInventory(array $vendors): array
    {
        $validatedProducts = [];

        foreach ($vendors as $vendorData) {
            foreach ($vendorData['items'] as $item) {
                $product = $this->productRepository->getByIdLocked($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception(
                        "Product '{$product->name}' has insufficient stock. "
                        . "Requested: {$item['quantity']}, Available: {$product->stock}"
                    );
                }
                $validatedProducts[$item['product_id']] = $product;
            }
        }

        return $validatedProducts;
    }
}

<?php

namespace App\Services;

use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\VoucherRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProcessCheckoutAction
{
    public function __construct(
        protected OrderRepositoryInterface   $orderRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CartRepositoryInterface $cartRepository,
        protected VoucherRepositoryInterface $voucherRepository
    ) {}

    public function execute(array $data)
    {
        return DB::transaction(function () use ($data) {

            // 1. Validate Stock & Load Products
            $validatedInventory = $this->validateAndLockInventory($data['vendors']);

            // 2. Process Vendors & Items (calculate real totals)
            $vendorOrdersData = [];
            $calculatedGrandTotal = 0;

            foreach ($data['vendors'] as $vendorId => $vendorData) {
                $itemsToInsert = [];
                $calculatedSubtotal = 0;

                foreach ($vendorData['items'] as $item) {
                    $variantId = $item['variant_id'] ?? null;
                    $productId = $item['product_id'];

                    if ($variantId) {
                        $variant = $validatedInventory['variants'][$variantId];
                        $correctPrice = (float) $variant->price;
                    } else {
                        $product = $validatedInventory['products'][$productId];
                        $correctPrice = (float) $product->discounted_price;
                    }

                    $itemsToInsert[] = [
                        'product_id'     => $productId,
                        'variant_id'     => $variantId,
                        'ordering_price' => $correctPrice,
                        'quantity'       => $item['quantity'],
                    ];

                    $calculatedSubtotal += $correctPrice * $item['quantity'];
                }

                // Handle Voucher Discount (if any)
                $discountAmount = 0;
                if (isset($vendorData['voucher_id'])) {
                    $voucher = $this->voucherRepository->findById($vendorData['voucher_id']);
                    if ($voucher && $calculatedSubtotal >= ($voucher->min_spend ?? 0)) {
                        if ($voucher->discount_type === 'percentage') {
                            $discountAmount = $calculatedSubtotal * ($voucher->discount_value / 100);
                        } else {
                            $discountAmount = $voucher->discount_value;
                        }
                        $discountAmount = min($calculatedSubtotal, $discountAmount);
                        $this->voucherRepository->incrementUsage($voucher->id);
                    }
                }

                $vendorOrdersData[] = [
                    'vendor_id' => $vendorId,
                    'subtotal'  => $calculatedSubtotal,
                    'discount_amount' => $discountAmount,
                    'voucher_id' => $vendorData['voucher_id'] ?? null,
                    'items' => $itemsToInsert,
                ];

                $calculatedGrandTotal += max(0, $calculatedSubtotal - $discountAmount);
            }

            // 3. Create Parent Order
            $order = $this->orderRepository->create([
                'user_id'          => $data['user_id'],
                'total_price'      => $calculatedGrandTotal,
                'payment_method'   => $data['payment_method'],
                'shipping_address' => $data['shipping_address'],
                'shipping_phone'   => $data['shipping_phone'],
                'status'           => 'pending',
            ]);

            // 4. Save Vendor Orders & Items
            foreach ($vendorOrdersData as $voData) {
                $vendorOrder = $this->orderRepository->createVendorOrder($order, [
                    'vendor_id' => $voData['vendor_id'],
                    'subtotal'  => $voData['subtotal'],
                    'status'    => 'pending',
                    'voucher_id' => $voData['voucher_id'],
                    'discount_amount' => $voData['discount_amount'],
                ]);

                $this->orderRepository->insertVendorOrderItems($vendorOrder, $voData['items']);
            }

            $this->cartRepository->clearCart($order->user_id);

            return $order->load('vendorOrders.items');
        });
    }

    private function validateAndLockInventory(array $vendors): array
    {
        $validatedInventory = [
            'products' => [],
            'variants' => [],
        ];

        $requestedQuantities = [
            'products' => [],
            'variants' => [],
        ];

        foreach ($vendors as $vendorData) {
            foreach ($vendorData['items'] as $item) {
                $variantId = $item['variant_id'] ?? null;
                $productId = $item['product_id'];

                if ($variantId) {
                    if (!isset($validatedInventory['variants'][$variantId])) {
                        $variant = $this->productRepository->getVariantByIdLocked($variantId);
                        $validatedInventory['variants'][$variantId] = $variant;
                        $requestedQuantities['variants'][$variantId] = 0;
                    }

                    $requestedQuantities['variants'][$variantId] += $item['quantity'];
                    $variant = $validatedInventory['variants'][$variantId];

                    if ($variant->stock < $requestedQuantities['variants'][$variantId]) {
                        throw new \Exception(
                            "Product Variant has insufficient stock. "
                            . "Requested total: {$requestedQuantities['variants'][$variantId]}, Available: {$variant->stock}"
                        );
                    }
                } else {
                    if (!isset($validatedInventory['products'][$productId])) {
                        $product = $this->productRepository->getByIdLocked($productId);
                        $validatedInventory['products'][$productId] = $product;
                        $requestedQuantities['products'][$productId] = 0;
                    }

                    $requestedQuantities['products'][$productId] += $item['quantity'];
                    $product = $validatedInventory['products'][$productId];

                    if ($product->stock < $requestedQuantities['products'][$productId]) {
                        throw new \Exception(
                            "Product '{$product->name}' has insufficient stock. "
                            . "Requested total: {$requestedQuantities['products'][$productId]}, Available: {$product->stock}"
                        );
                    }
                }
            }
        }

        return $validatedInventory;
    }
}

<?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        if ($orderItem->variant_id) {
            $variant = $orderItem->variant;
            if ($variant) {
                $variant->stock -= $orderItem->quantity;
                $variant->save(); // This will definitely trigger ProductVariantObserver
            }
        } else {
            $product = $orderItem->product;
            if ($product) {
                $product->stock -= $orderItem->quantity;
                $product->save();
            }
        }
    }
}

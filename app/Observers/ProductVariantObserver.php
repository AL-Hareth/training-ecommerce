<?php

namespace App\Observers;

use App\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "saved" event.
     */
    public function saved(ProductVariant $productVariant): void
    {
        $this->syncProductStock($productVariant);
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $productVariant): void
    {
        $this->syncProductStock($productVariant);
    }

    /**
     * Sync the parent product's overall stock.
     */
    protected function syncProductStock(ProductVariant $productVariant): void
    {
        $product = $productVariant->product;
        if ($product) {
            // We use a query to get the sum to avoid issues with stale related models
            $totalStock = $product->variants()->sum('stock');
            
            // We use update quietly or check if value changed to avoid unnecessary events
            // though Product doesn't have an observer yet that would cause a loop.
            if ($product->stock !== (int) $totalStock) {
                $product->update(['stock' => $totalStock]);
            }
        }
    }
}

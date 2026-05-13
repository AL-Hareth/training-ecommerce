<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface {
    protected $model;

    public function __construct(Cart $cart) {
        $this->model = $cart;
    }


    public function getCurrentCart(?string $userId)
    {
        $data = $this
            ->model
            ->query()
            ->with(['items.product.vendor'])
            ->where('user_id', $userId)
            ->first();
        // Flatten the image from the product
        $data->items->transform(function ($item) {
            $item->product->image = $item->product->getFirstMediaUrl('images', 'thumb');
            return $item;
        });

        return $data;
    }

    public function createCart(?string $userId) {
        if ($this->model->query()->where('user_id', $userId)->exists()) {
            return $this->model->query()->where('user_id', $userId)->first();
        }

        return $this->model->query()->create(
            ['user_id' => $userId]
        );
    }

    public function addItem(string $cartId, string $productId, int $quantity = 1)
    {
        $cart = $this->model->query()->findOrFail($cartId);
        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return $cart;
    }

    public function removeItem(string $userId, string $cartItemId)
    {
        return $this->getCurrentCart($userId)->items()->findOrFail($cartItemId)->delete();
    }

    public function clearCart(string $userId)
    {
        return $this->getCurrentCart($userId)->items()->delete();
    }
}

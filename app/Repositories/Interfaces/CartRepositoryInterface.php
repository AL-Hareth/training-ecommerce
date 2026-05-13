<?php

namespace App\Repositories\Interfaces;

use App\Models\Cart;

interface CartRepositoryInterface {
    public function getCurrentCart(string|null $userId);
    public function createCart(string|null $userId);
    public function addItem(string $cartId, string $productId, int $quantity = 1);
    public function removeItem(string $userId, string $cartItemId);
    public function clearCart(string $userId);
}

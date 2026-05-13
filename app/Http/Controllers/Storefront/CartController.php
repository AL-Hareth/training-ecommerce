<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    protected $cartRepository;
    public function __construct(CartRepositoryInterface $cartRepository) {
        $this->cartRepository = $cartRepository;
    }

    public function index() {
        return Inertia::render('Storefront/Cart/Index', [
            'cart' => $this->cartRepository->getCurrentCart(Auth::id()),
        ]);
    }

    public function store(StoreCartItemRequest $request) {
        $cart = $this->cartRepository->createCart(Auth::id());
        $this->cartRepository->addItem($cart->id, $request->validated('product_id'), (int) $request->validated('quantity'));

        return back()->with('toast', [
            'message'=> 'Item added to cart!',
            'type' => 'success',
        ]);
    }

    public function destroy(string $itemId) {
        $this->cartRepository->removeItem(Auth::id(), $itemId);

        return redirect()->route('cart.index');
    }

    public function clear() {
        $this->cartRepository->clearCart(Auth::id());
        return redirect()->route('cart.index')->with('toast', [
            'message' => 'Cart cleared!',
            'type' => 'success',
        ]);
    }
}

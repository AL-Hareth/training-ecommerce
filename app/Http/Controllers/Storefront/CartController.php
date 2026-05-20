<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\VoucherRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    protected $cartRepository;
    protected $voucherRepository;

    public function __construct(CartRepositoryInterface $cartRepository, VoucherRepositoryInterface $voucherRepository) {
        $this->cartRepository = $cartRepository;
        $this->voucherRepository = $voucherRepository;
    }

    public function index() {
        $appliedVouchers = $this->voucherRepository->getByIds(array_values(session()->get('vouchers', [])));

        return Inertia::render('Storefront/Cart/Index', [
            'cart' => $this->cartRepository->getCurrentCart(Auth::id()),
            'appliedVouchers' => $appliedVouchers,
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
        session()->forget('vouchers');
        return redirect()->route('cart.index')->with('toast', [
            'message' => 'Cart cleared!',
            'type' => 'success',
        ]);
    }

    public function applyVoucher(Request $request) {
        $request->validate(['code' => 'required|string']);

        $voucher = $this->voucherRepository->findValidByCode($request->code);

        if (!$voucher || ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit)) {
            return back()->with('toast', ['message' => 'Invalid or expired voucher', 'type' => 'error']);
        }

        $vouchers = session()->get('vouchers', []);
        $vouchers[$voucher->vendor_id] = $voucher->id;
        session()->put('vouchers', $vouchers);

        return back()->with('toast', ['message' => 'Voucher applied', 'type' => 'success']);
    }

    public function removeVoucher(string $vendorId) {
        $vouchers = session()->get('vouchers', []);
        unset($vouchers[$vendorId]);
        session()->put('vouchers', $vouchers);

        return back()->with('toast', ['message' => 'Voucher removed', 'type' => 'success']);
    }
}

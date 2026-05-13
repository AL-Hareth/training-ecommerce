<?php

namespace App\Http\Controllers\Storefront;

use App\Services\ProcessCheckoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected $orderRepository;
    protected $cartRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, CartRepositoryInterface $cartRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    public function index() {
        $orders = $this->orderRepository->getPaginatedForUser(Auth::id());
        return Inertia::render('Storefront/Order/Index', [
            'orders' => $orders
        ]);
    }

    public function show(string $orderId) {
        return Inertia::render('Storefront/Order/Show', [
            'order' => $this->orderRepository->getOrderFullDetails($orderId),
        ]);
    }

    public function create()
    {
        $checkoutData = $this->buildCheckoutData();
        return Inertia::render('Storefront/Checkout/Create',[
            'checkoutData' => $checkoutData,
        ]);
    }

    public function store(StoreOrderRequest $request, ProcessCheckoutAction $checkoutAction) {
        $validatedData = $request->validated();

        try {
            // $this->orderRepository->createCheckoutOrder($validatedData);
            // $this->cartRepository->clearCart(Auth::id());
            $checkoutAction->execute($validatedData);

            return redirect()->route('products.index')
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    private function buildCheckoutData()
    {
        // 1. Fetch the user's cart.
        // Eager load the nested relationships so we don't cause an N+1 query problem.
        $cart = $this->cartRepository->getCurrentCart(Auth::id());

        // If the cart is empty or doesn't exist, handle it (e.g., redirect back)
        if (!$cart || $cart->items->isEmpty()) {
            abort(400, 'Your cart is empty.');
        }

        // 2. Group the flat cart items by the Vendor's ID
        $groupedItems = $cart->items->groupBy('product.vendor.id');

        $vendorsArray = [];
        $grandTotal = 0;

        // 3. Loop through each vendor's group of items
        foreach ($groupedItems as $vendorId => $items) {

            // Map the items into the exact format needed for the order
            $formattedItems = $items->map(function ($cartItem) {
                return [
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    // CRITICAL: Always use the product's CURRENT price, not a saved cart price!
                    'price' => $cartItem->product->price,
                    'quantity' => $cartItem->quantity,
                ];
            });

            // Calculate the subtotal for just this vendor's items
            $vendorSubtotal = $formattedItems->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            // --- BUSINESS LOGIC HERE ---
            // You can query dynamic shipping rates or use flat rates.
            $vendorShippingFee = 15.00;

            // Calculate your platform's cut (e.g., 10% of the subtotal)
            $commission = $vendorSubtotal * 0.10;

            // What the vendor actually takes home
            $vendorEarnings = ($vendorSubtotal + $vendorShippingFee) - $commission;

            // 4. Build the vendor's node in the array
            $vendorsArray[$vendorId] = [
                'vendor_id' => $vendorId,
                'vendor_name' => $items->first()->product->vendor->name, // Grab name from the first item
                'subtotal' => $vendorSubtotal,
                'shipping_fee' => $vendorShippingFee,
                'commission_amount' => $commission,
                'vendor_earnings' => $vendorEarnings,
                'items' => $formattedItems->toArray(),
            ];

            // 5. Add this vendor's total to the customer's Grand Total
            $grandTotal += ($vendorSubtotal + $vendorShippingFee);
        }

        // 6. Return the final structured array
        return [
            'total_price' => $grandTotal,
            // Using array_values removes the string UUID keys so Vue receives a clean, zero-indexed array
            'vendors' => $vendorsArray,
        ];
    }
}

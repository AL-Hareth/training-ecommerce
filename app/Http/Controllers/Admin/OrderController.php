<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository) {
        $this->orderRepository = $orderRepository;
    }

    public function index(Request $request) {
        $searchTerm = trim((string) $request->input('q', ''));

        if (Auth::user()->role === 'admin') {
            return Inertia::render('Admin/Order/Index', [
                'orders' => $this->orderRepository->getAll($searchTerm),
                'q' => $searchTerm,
            ]);
        }

        return Inertia::render('Admin/Order/Index', [
            'orders' => $this->orderRepository->getForVendorId(Auth::id(), $searchTerm),
            'q' => $searchTerm,
        ]);
    }

    public function show(string $orderId) {
        return Inertia::render('Admin/Order/Show', [
            'order' => $this->orderRepository->getOrderFullDetails($orderId),
        ]);
    }

    public function edit(string $orderId) {
        return Inertia::render('Admin/Order/Edit', [
            'order' => $this->orderRepository->getOrderFullDetails($orderId),
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, string $orderId) {
        $validated = $request->validated();
        $this->orderRepository->updateOrderStatus($orderId, $validated['status']);
    }
}

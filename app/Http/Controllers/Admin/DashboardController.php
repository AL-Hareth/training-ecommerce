<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $orderRepository;
    protected $productRepository;
    protected $userRepository;
    protected $categoryRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository,
        CategoryRepositoryInterface $categoryRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function index() {
        $isVendor = Auth::user()->role === 'vendor';

        $totalProducts = $isVendor
            ? $this->productRepository->getByVendorId(Auth::id())->count()
            : count($this->productRepository->getAll());

        $totalOrders = $isVendor
            ? $this->orderRepository->getForVendorId(Auth::id())->count()
            : $this->orderRepository->getAll()->count();

        return Inertia::render('Admin/Dashboard', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalUsers' => count($this->userRepository->getAll()),
            'totalCategories' => count($this->categoryRepository->getAll()),
        ]);
    }
}

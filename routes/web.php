<?php

use App\Http\Controllers\Admin\AttributeController as AdminAttributeController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Storefront\OrderController as StorefrontOrderController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\CartController as StorefrontCartController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy']);

Route::prefix('admin')
    ->middleware(['auth', 'is-admin'])
    ->group(function () {
        Route::prefix('categories')
            ->controller(AdminCategoryController::class)
            ->group(function () {
            Route::get('/', 'index')->name('admin.categories.index');
            Route::get('/create', 'create')->name('admin.categories.create');
            Route::get('/{categoryId}/edit', 'edit')->name('admin.categories.edit');
            Route::post('/store', 'store')->name('admin.categories.store');
            Route::put('/{categoryId}', 'update')->name('admin.categories.update');
            Route::delete('/{categoryId}', 'destroy')->name('admin.categories.destroy');
        });

        Route::prefix('users')
            ->controller(AdminUserController::class)
            ->group(function () {
                Route::get('/', 'index')->name('admin.users.index');
                Route::get('/{userId}/edit', 'edit')->name('admin.users.edit');
                Route::put('/{userId}', 'update')->name('admin.users.update');
                Route::delete('/{userId}', 'destroy')->name('admin.users.destroy');
            });

        Route::prefix('orders')
            ->controller(AdminOrderController::class)
            ->group(function () {
                Route::put('/{orderId}', 'update')->name('admin.orders.update');
                Route::get('/{orderId}/edit', 'edit')->name('admin.orders.edit');
            });

        Route::prefix('attributes')
            ->controller(AdminAttributeController::class)
            ->group(function () {
                Route::get('/', 'index')->name('admin.attributes.index');
                Route::get('/create', 'create')->name('admin.attributes.create');
                Route::post('/', 'store')->name('admin.attributes.store');
                Route::get('/{attributeId}/edit', 'edit')->name('admin.attributes.edit');
                Route::put('/{attributeId}', 'update')->name('admin.attributes.update');
                Route::delete('/{attributeId}', 'destroy')->name('admin.attributes.destroy');
            });
    });

Route::prefix('admin')
    ->middleware(['auth', 'is-vendor'])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('products')
            ->controller(AdminProductController::class)
            ->group(function () {
            Route::get('/', 'index')->name('admin.products.index');
            Route::get('/create', 'create')->name('admin.products.create');
            Route::get('/{productId}/edit', 'edit')->name('admin.products.edit');
            Route::put('/{productId}', 'update')->name('admin.products.update');
            Route::post('/', 'store')->name('admin.products.store');
            Route::delete('/{productId}', 'destroy')->name('admin.products.destroy');
        });

        Route::prefix('orders')
            ->controller(AdminOrderController::class)
            ->group(function () {
            Route::get('/', 'index')->name('admin.orders.index');
            Route::get('/{orderId}', 'show')->name('admin.orders.show');
        });

        Route::prefix('vouchers')
            ->controller(\App\Http\Controllers\Admin\VoucherController::class)
            ->group(function () {
            Route::get('/', 'index')->name('admin.vouchers.index');
            Route::get('/create', 'create')->name('admin.vouchers.create');
            Route::get('/{voucherId}/edit', 'edit')->name('admin.vouchers.edit');
            Route::put('/{voucherId}', 'update')->name('admin.vouchers.update');
            Route::post('/', 'store')->name('admin.vouchers.store');
            Route::delete('/{voucherId}', 'destroy')->name('admin.vouchers.destroy');
        });
    });

Route::controller(StorefrontProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::get('/', 'index')->name('products.index');
        Route::get('/{productId}', 'show')->name('products.show');
    });

Route::controller(StorefrontCartController::class)
    ->middleware(['auth'])
    ->prefix('cart')
    ->group(function () {
        Route::get('/', 'index')->name('cart.index');
        Route::delete('/{itemId}', 'destroy')->name('cart.destroy');
        Route::post('/add', 'store')->name('cart.store');
        Route::post('/clear', 'clear')->name('cart.clear');
        Route::post('/voucher', 'applyVoucher')->name('cart.voucher');
        Route::delete('/voucher/{vendorId}', 'removeVoucher')->name('cart.voucher.remove');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [StorefrontOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderId}', [StorefrontOrderController::class, 'show'])->name('orders.show');
    Route::get('/checkout', [StorefrontOrderController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [StorefrontOrderController::class, 'store'])->name('checkout.store');
});

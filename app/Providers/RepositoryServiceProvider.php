<?php

namespace App\Providers;

use App\Repositories\AttributeRepository;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Decorators\CachingCategoryRepository;
use App\Repositories\Decorators\CachingProductsRepository;
use App\Repositories\Decorators\CachingUserRepository;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\VoucherRepositoryInterface::class, \App\Repositories\VoucherRepository::class);

        // Bind concrete ProductRepository before the cached decorator to break circular dependency
        $this->app->bind(ProductRepository::class, function ($app) {
            return new ProductRepository(
                new \App\Models\Product(),
                new \App\Models\ProductVariant(),
                $app->make(AttributeRepository::class)
            );
        });

        $this->app->singleton(ProductRepositoryInterface::class, function ($app) {
            $baseRepo = $app->make(ProductRepository::class);
            return new CachingProductsRepository($baseRepo);
        });

        $this->app->singleton(CategoryRepositoryInterface::class, function ($app) {
            $baseRepo = $this->app->make(CategoryRepository::class);
            return new CachingCategoryRepository($baseRepo);
        });

        $this->app->singleton(UserRepositoryInterface::class, function ($app) {
            $baseRepo = $this->app->make(UserRepository::class);
            return new CachingUserRepository($baseRepo);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

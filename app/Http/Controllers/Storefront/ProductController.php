<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected AttributeRepositoryInterface $attributeRepository,
    ) {}

    public function index(Request $request)
    {
        $searchTerm        = trim((string) $request->input('q', ''));
        $page              = (int) $request->input('page', 1);
        $limit             = (int) $request->input('limit', 12);
        $attributeValueIds = (array) $request->input('attributes', []);

        $products = $this->productRepository->getAll(
            $searchTerm,
            $page,
            $limit,
            $attributeValueIds,
        );

        $total = count(
            $this->productRepository->getAll($searchTerm, null, null, $attributeValueIds)
        );

        return Inertia::render('Storefront/Product/Index', [
            'products'         => $products,
            'q'                => $searchTerm,
            'page'             => $page,
            'limit'            => $limit,
            'total'            => $total,
            'attributes'       => $this->attributeRepository->getAll(),
            'activeAttributes' => $attributeValueIds,
        ]);
    }

    public function show(string $productId)
    {
        $product = $this->productRepository->getById($productId);
        $imageUrl = $product->getFirstMediaUrl('images');
        $product->image = $imageUrl;

        return Inertia::render('Storefront/Product/Show', [
            'product' => $product,
        ]);
    }
}

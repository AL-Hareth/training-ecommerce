<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProductController extends Controller
{
    protected $productRepository;
    protected $categoryRepository;
    protected $attributeRepository;
    public function __construct(ProductRepositoryInterface $productRepository, CategoryRepositoryInterface $categoryRepository, AttributeRepositoryInterface $attributeRepository)
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function index(Request $request) {
        $searchTerm = trim((string) $request->input('q', ''));

        return Inertia::render('Admin/Product/Index', [
            'products' => Auth::user()->role === 'admin'
                ? $this->productRepository->getAll($searchTerm)
                : $this->productRepository->getByVendorId(Auth::id(), $searchTerm),
            'q' => $searchTerm,
        ]);
    }

    public function create() {
        return Inertia::render('Admin/Product/Create', [
            'categories' => $this->categoryRepository->getAll(),
            'attributes' => $this->attributeRepository->getAll(),
        ]);
    }

    public function store(StoreProductRequest $request) {
        Gate::authorize('create', Product::class);

        $validated = $request->validated();
        $validated['vendor_id'] = Auth::id();

        $product = $this->productRepository->create($validated);
        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('images');
            $product->save();
        }

        // Sync attribute values
        $product->attributeValues()->sync($request->input('attribute_value_ids', []));

        return redirect()->route('admin.products.index');
    }

    public function edit(string $productId) {
        $product = $this->productRepository->getById($productId);
        $product->load('attributeValues');
        return Inertia::render('Admin/Product/Edit', [
            'product'              => $product,
            'categories'           => $this->categoryRepository->getAll(),
            'attributes'           => $this->attributeRepository->getAll(),
            'activeAttributeValues' => $product->attributeValues->pluck('id'),
        ]);
    }

    public function update(UpdateProductRequest $request, string $productId) {
        Gate::authorize('update', $this->productRepository->getById($productId));
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images', 'public');
        }
        $this->productRepository->update($productId, $validated);

        // Sync attribute values
        $product = $this->productRepository->getById($productId);
        $product->attributeValues()->sync($request->input('attribute_value_ids', []));

        return redirect()->route('admin.products.index');
    }

    public function destroy(string $productId) {
        $this->productRepository->delete($productId);
        return redirect()->route('admin.products.index');
    }
}

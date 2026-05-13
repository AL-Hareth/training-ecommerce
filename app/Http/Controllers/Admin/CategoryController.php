<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    protected $categoryRepository;
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index(Request $request) {
        $searchTerm = trim((string) $request->input('q', ''));

        return Inertia::render('Admin/Category/Index', [
            "categories" => $this->categoryRepository->getAll($searchTerm),
            "q" => $searchTerm,
        ]);
    }

    public function destroy(string $id) {
        $this->categoryRepository->delete($id);
        return redirect()->route('admin.categories.index');
    }

    public function create() {
        return Inertia::render('Admin/Category/Create');
    }

    public function store(StoreCategoryRequest $request) {
        $validated = $request->validated();
        $this->categoryRepository->create($validated);
        return redirect()->route('admin.categories.index');
    }

    public function edit(string $categoryId) {
        return Inertia::render('Admin/Category/Edit', [
            "category" => $this->categoryRepository->getById($categoryId),
        ]);
    }

    public function update(UpdateCategoryRequest $request, string $categoryId) {
        $validated = $request->validated();
        $this->categoryRepository->update($categoryId, $validated);
        return redirect()->route('admin.categories.index');
    }
}

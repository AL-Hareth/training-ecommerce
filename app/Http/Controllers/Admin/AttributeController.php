<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttributeController extends Controller
{
    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Admin/Attribute/Index', [
            'attributes' => $this->attributeRepository->getAll(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Attribute/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'slug'     => 'nullable|string|max:255|unique:attributes,slug',
            'values'   => 'nullable|array',
            'values.*' => 'string|max:255',
        ]);

        $attribute = $this->attributeRepository->create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
        ]);

        foreach ($validated['values'] ?? [] as $value) {
            $this->attributeRepository->addValue($attribute->id, $value);
        }

        return redirect()->route('admin.attributes.index');
    }

    public function edit(string $attributeId)
    {
        return Inertia::render('Admin/Attribute/Edit', [
            'attribute' => $this->attributeRepository->getById($attributeId),
        ]);
    }

    public function update(Request $request, string $attributeId)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => "nullable|string|max:255|unique:attributes,slug,{$attributeId},id",
            'new_values'    => 'nullable|array',
            'new_values.*'  => 'string|max:255',
            'delete_values' => 'nullable|array',
            'delete_values.*' => 'uuid',
        ]);

        $this->attributeRepository->update($attributeId, [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
        ]);

        foreach ($validated['new_values'] ?? [] as $value) {
            $this->attributeRepository->addValue($attributeId, $value);
        }

        foreach ($validated['delete_values'] ?? [] as $valueId) {
            $this->attributeRepository->deleteValue($valueId);
        }

        return redirect()->route('admin.attributes.index');
    }

    public function destroy(string $attributeId)
    {
        $this->attributeRepository->delete($attributeId);
        return redirect()->route('admin.attributes.index');
    }
}

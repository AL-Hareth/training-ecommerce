<?php

namespace App\Repositories;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface {
    protected $model;
    protected $variantModel;
    protected $attributeRepository;

    public function __construct(Product $product, ProductVariant $variantModel, AttributeRepositoryInterface $attributeRepository)
    {
        $this->model = $product;
        $this->variantModel = $variantModel;
        $this->attributeRepository = $attributeRepository;
    }

    public function getAll(?string $searchTerm = '', int|null $page = null, int|null $limit = null, array $attributeValueIds = [])
    {
        $scout = $this->model->search($searchTerm);

        if (!empty($attributeValueIds)) {
            $grouped = $this->attributeRepository->getGroupedValuesByIds($attributeValueIds);

            $scout->query(function ($query) use ($grouped) {
                foreach ($grouped as $attributeId => $values) {
                    $ids = $values->pluck('id')->toArray();
                    $query->whereHas('attributeValues', function ($q) use ($ids) {
                        $q->whereIn('attribute_values.id', $ids);
                    });
                }
                $query->with(['category', 'attributeValues.attribute', 'variants']);
            });
        } else {
            $scout->query(function ($query) {
                $query->with(['category', 'attributeValues.attribute', 'variants']);
            });
        }

        $scout->orderBy('created_at', 'desc');

        if ($page && $limit) {
            $paginator = $scout->paginate($limit, 'page', $page);
            $data = collect($paginator->items());
        } else {
            $data = $scout->get();
        }

        $data->transform(function ($item) {
            $item->image = $item->getFirstMediaUrl('images', 'thumb');
            return $item;
        });

        return $data;
    }

    public function getByVendorId(string $vendorId, ?string $searchTerm = '')
    {
        $scout = $this->model->search($searchTerm);

        $scout->query(function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId)->with(['category', 'attributeValues.attribute', 'variants']);
        });

        $scout->orderBy('created_at', 'desc');
        $data = $scout->get();

        $data->transform(function ($item) {
            $item->image = $item->getFirstMediaUrl('images', 'thumb');
            return $item;
        });

        return $data;
    }

    public function getById($id)
    {
        return $this->model->query()->with(['category', 'vendor', 'attributeValues.attribute', 'variants'])->findOrFail($id);
    }

    public function getByIdLocked($id)
    {
        return $this->model->query()->with(['category', 'vendor', 'attributeValues.attribute', 'variants'])->lockForUpdate()->findOrFail($id);
    }

    public function getVariantByIdLocked(string $id)
    {
        return $this->variantModel->query()->lockForUpdate()->findOrFail($id);
    }

    public function create(array $attributes)
    {
        $variants = $attributes['variants'] ?? [];
        unset($attributes['variants']);

        $product = $this->model->query()->create($attributes);

        if (!empty($variants)) {
            $product->variants()->createMany($variants);
        }

        return $product;
    }

    public function update($id, array $attributes)
    {
        $variants = $attributes['variants'] ?? [];
        unset($attributes['variants']);

        $product = $this->model->query()->findOrFail($id);
        $product->update($attributes);

        if (!empty($variants)) {
            // Simple approach: delete old variants and create new ones
            // Or more complex: match by ID and update/create/delete
            // Given the UpdateProductRequest has variants.*.id, we should probably be more careful.
            
            $existingVariantIds = $product->variants()->pluck('id')->toArray();
            $newVariantIds = collect($variants)->pluck('id')->filter()->toArray();
            
            // Delete variants not in the new list
            $product->variants()->whereNotIn('id', $newVariantIds)->delete();
            
            foreach ($variants as $variantData) {
                if (isset($variantData['id'])) {
                    $variant = $product->variants()->findOrFail($variantData['id']);
                    $variant->update([
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'attributes' => $variantData['attributes'],
                    ]);
                } else {
                    $product->variants()->create($variantData);
                }
            }
        }

        return $product;
    }

    public function updateVariantStock(string $variantId, int $quantity)
    {
        $variant = $this->variantModel->query()->findOrFail($variantId);
        $variant->update(['stock' => $quantity]);
        return $variant;
    }

    public function delete($id)
    {
        return $this->model->query()->findOrFail($id)->delete();
    }

    public function countByAttributeValueId(string $attributeValueId): int
    {
        return $this->model->search('')
            ->where('attribute_values', $attributeValueId)
            ->keys()
            ->count();
    }
}

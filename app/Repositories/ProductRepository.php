<?php

namespace App\Repositories;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface {
    protected $model;
    protected $attributeRepository;

    public function __construct(Product $product, AttributeRepositoryInterface $attributeRepository)
    {
        $this->model = $product;
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
                $query->with(['category', 'attributeValues.attribute']);
            });
        } else {
            $scout->query(function ($query) {
                $query->with(['category', 'attributeValues.attribute']);
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
            $query->where('vendor_id', $vendorId)->with('category');
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
        return $this->model->query()->with(['category', 'vendor', 'attributeValues.attribute'])->findOrFail($id);
    }

    public function getByIdLocked($id)
    {
        return $this->model->query()->with(['category', 'vendor', 'attributeValues.attribute'])->lockForUpdate()->findOrFail($id);
    }

    public function create(array $attributes)
    {
        return $this->model->query()->create($attributes);
    }

    public function update($id, array $attributes)
    {
        return $this->model->query()->findOrFail($id)->update($attributes);
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

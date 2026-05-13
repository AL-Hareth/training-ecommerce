<?php

namespace App\Repositories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class AttributeRepository implements AttributeRepositoryInterface
{
    public function __construct(
        protected Attribute $model,
        protected AttributeValue $valueModel,
    ) {}

    protected function getProductRepository(): ProductRepositoryInterface
    {
        return app(ProductRepositoryInterface::class);
    }

    public function getAll()
    {
        $attributes = $this->model->query()->with('values')->orderBy('name')->get();

        // Count products using Scout search to ensure consistency with filtering
        $productRepo = $this->getProductRepository();
        $attributes->transform(function ($attribute) use ($productRepo) {
            $attribute->values->transform(function ($value) use ($productRepo) {
                $count = $productRepo->countByAttributeValueId($value->id);

                $value->setAttribute('count', $count);
                return $value;
            });
            return $attribute;
        });

        return $attributes;
    }

    public function getById(string $id)
    {
        return $this->model->query()->with('values')->findOrFail($id);
    }

    public function create(array $attributes)
    {
        return $this->model->query()->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $attr = $this->model->query()->findOrFail($id);
        $attr->update($attributes);
        return $attr;
    }

    public function delete(string $id)
    {
        return $this->model->query()->findOrFail($id)->delete();
    }

    public function addValue(string $attributeId, string $value)
    {
        return $this->valueModel->query()->create([
            'attribute_id' => $attributeId,
            'value'        => $value,
        ]);
    }

    public function deleteValue(string $valueId)
    {
        return $this->valueModel->query()->findOrFail($valueId)->delete();
    }

    public function getGroupedValuesByIds(array $attributeValueIds)
    {
        return $this->valueModel->query()
            ->whereIn('id', $attributeValueIds)
            ->get()
            ->groupBy('attribute_id');
    }
}

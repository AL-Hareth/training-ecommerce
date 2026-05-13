<?php

namespace App\Repositories\Interfaces;

interface AttributeRepositoryInterface
{
    public function getAll();
    public function getById(string $id);
    public function create(array $attributes);
    public function update(string $id, array $attributes);
    public function delete(string $id);
    public function addValue(string $attributeId, string $value);
    public function deleteValue(string $valueId);
}

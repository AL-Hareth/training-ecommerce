<?php

namespace App\Repositories\Interfaces;

interface CategoryRepositoryInterface {
    public function getAll(?string $searchTerm = '');
    public function getById(string $id);
    public function create(array $attributes);
    public function update(string $id, array $attributes);
    public function delete(string $id);
}

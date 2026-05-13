<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface {
    protected $model;

    public function __construct(Category $user)
    {
        $this->model = $user;
    }

    public function getAll(?string $searchTerm = '')
    {
        if ($searchTerm) {
            return $this->model->search($searchTerm)
                ->orderBy('updated_at')
                ->get();
        }

        return $this->model->query()->orderBy('updated_at')->get();
    }

    public function getById($id)
    {
        return $this->model->query()->findOrFail($id);
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
}

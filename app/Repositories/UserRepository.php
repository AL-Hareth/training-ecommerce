<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function getAll(?string $searchTerm = '')
    {
        if ($searchTerm) {
            return $this->model->search($searchTerm)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return $this->model->query()->orderBy('created_at', 'desc')->get();
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request) {
        $searchTerm = trim((string) $request->input('q', ''));

        return Inertia::render('Admin/User/Index', [
            'users' => $this->userRepository->getAll($searchTerm),
            'q' => $searchTerm,
        ]);
    }

    public function edit(string $id) {
        return Inertia::render('Admin/User/Edit', [
            'user' => $this->userRepository->getById($id),
        ]);
    }

    public function update(UpdateUserRequest $request, string $id) {
        $validated = $request->validated();
        $this->userRepository->update($id, $validated);
        return redirect()->route('admin.users.index');
    }

    public function destroy(string $id) {
        $this->userRepository->delete($id);
        return redirect()->route('admin.users.index');
    }
}

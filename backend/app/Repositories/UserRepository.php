<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\UserModel as User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private User $model
    ) {}

    public function findByEmail(string $email): ?User
    {
        return $this->model->with('role')->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        $user = $this->model->create($data);
        return $user->load('role');
    }

    public function find(int $id): ?User
    {
        return $this->model->with('role')->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->model->find($id);
        if (!$user) {
            return false;
        }
        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        $user = $this->model->find($id);
        if (!$user) {
            return false;
        }
        return $user->delete();
    }

    public function all(): array
    {
        return $this->model->all()->toArray();
    }
}

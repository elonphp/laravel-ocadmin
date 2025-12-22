<?php

namespace Elonphp\LaravelOcadminModules\Modules\User;

use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * User model class.
     */
    protected string $model;

    public function __construct()
    {
        $this->model = config('ocadmin.models.user', \App\Models\User::class);
    }

    /**
     * Create a new user.
     */
    public function create(array $data): mixed
    {
        $data['password'] = Hash::make($data['password']);

        return $this->model::create($data);
    }

    /**
     * Update a user.
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->model::findOrFail($id);

        // Only hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $user->update($data);
    }

    /**
     * Delete a user.
     */
    public function delete(int $id): bool
    {
        $user = $this->model::findOrFail($id);

        return $user->delete();
    }

    /**
     * Delete multiple users.
     */
    public function deleteMultiple(array $ids): int
    {
        return $this->model::whereIn('id', $ids)->delete();
    }

    /**
     * Find a user by ID.
     */
    public function find(int $id): mixed
    {
        return $this->model::findOrFail($id);
    }

    /**
     * Get a new model instance with defaults.
     */
    public function withDefaults(): mixed
    {
        return new $this->model([
            'name' => '',
            'email' => '',
        ]);
    }
}

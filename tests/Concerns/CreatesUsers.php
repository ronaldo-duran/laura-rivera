<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\RolesAndPermissionsSeeder;

trait CreatesUsers
{
    protected function seedRoles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function userWithRole(string $role, array $attributes = []): User
    {
        $this->seedRoles();

        return tap(User::factory()->create($attributes))->assignRole($role);
    }

    protected function admin(array $attributes = []): User
    {
        return $this->userWithRole(Permissions::ROLE_ADMIN, $attributes);
    }

    protected function accountant(): User
    {
        return $this->userWithRole(Permissions::ROLE_ACCOUNTING);
    }

    protected function marketer(): User
    {
        return $this->userWithRole(Permissions::ROLE_MARKETING);
    }
}

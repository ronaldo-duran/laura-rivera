<?php

namespace Database\Seeders;

use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea/actualiza permisos y roles base. Idempotente: se puede correr en cada despliegue.
 * No quita permisos que un administrador haya agregado manualmente a contabilidad o marketing.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(Permissions::all()) as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (Permissions::defaultRoles() as $name => $def) {
            $role = Role::findOrCreate($name, 'web');

            if ($name === Permissions::ROLE_ADMIN) {
                $role->syncPermissions(Permission::all());
            } elseif ($role->wasRecentlyCreated) {
                $role->syncPermissions($def['permissions']);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

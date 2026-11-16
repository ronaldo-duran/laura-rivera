<?php

namespace Tests\Feature\Admin;

use App\Support\Permissions;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_seeder_creates_default_roles_and_is_idempotent(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(count(Permissions::all()), Permission::count());
        $this->assertSame(3, Role::count());

        $accounting = Role::findByName(Permissions::ROLE_ACCOUNTING);
        $this->assertTrue($accounting->hasPermissionTo(Permissions::DONATIONS_VIEW));
        $this->assertFalse($accounting->hasPermissionTo(Permissions::NEWS_MANAGE));

        $marketing = Role::findByName(Permissions::ROLE_MARKETING);
        $this->assertTrue($marketing->hasPermissionTo(Permissions::NEWS_MANAGE));
        $this->assertFalse($marketing->hasPermissionTo(Permissions::DONATIONS_VIEW));
    }

    public function test_seeder_keeps_manual_changes_to_non_admin_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        Role::findByName(Permissions::ROLE_MARKETING)->givePermissionTo(Permissions::VOLUNTEERS_VIEW);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertTrue(Role::findByName(Permissions::ROLE_MARKETING)->hasPermissionTo(Permissions::VOLUNTEERS_VIEW));
    }

    public function test_admin_creates_custom_role(): void
    {
        $this->actingAs($this->admin())->post(route('admin.roles.store'), [
            'name' => 'coordinación',
            'permissions' => [Permissions::DASHBOARD_VIEW, Permissions::VOLUNTEERS_VIEW],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('coordinación');
        $this->assertTrue($role->hasPermissionTo(Permissions::VOLUNTEERS_VIEW));
        $this->assertFalse($role->hasPermissionTo(Permissions::DONATIONS_VIEW));
    }

    public function test_admin_role_cannot_be_edited_or_deleted(): void
    {
        $admin = $this->admin();
        $role = Role::findByName(Permissions::ROLE_ADMIN);

        $this->actingAs($admin)->put(route('admin.roles.update', $role), ['name' => 'otro', 'permissions' => []])
            ->assertSessionHasErrors('name');
        $this->actingAs($admin)->delete(route('admin.roles.destroy', $role))->assertSessionHasErrors('role');

        $this->assertTrue(Role::where('name', Permissions::ROLE_ADMIN)->exists());
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $this->marketer();

        $this->actingAs($admin)->delete(route('admin.roles.destroy', Role::findByName(Permissions::ROLE_MARKETING)))
            ->assertSessionHasErrors('role');
    }

    public function test_empty_role_can_be_deleted(): void
    {
        $admin = $this->admin();
        $role = Role::create(['name' => 'temporal', 'guard_name' => 'web']);

        $this->actingAs($admin)->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
        $this->assertModelMissing($role);
    }

    public function test_admin_has_permissions_added_in_the_future(): void
    {
        $admin = $this->admin();
        Permission::findOrCreate('modulo.futuro', 'web');

        $this->assertTrue($admin->can('modulo.futuro'));
    }
}

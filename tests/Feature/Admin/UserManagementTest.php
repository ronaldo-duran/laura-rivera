<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_admin_sees_user_list(): void
    {
        $admin = $this->admin(['name' => 'Ana Admin']);

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()->assertSee('Ana Admin');
    }

    public function test_admin_creates_user_with_role(): void
    {
        $pw = $this->makePassword();
        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'Carlos Contador',
            'email' => 'carlos@example.com',
            'password' => $pw,
            'password_confirmation' => $pw,
            'roles' => [Permissions::ROLE_ACCOUNTING],
            'active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'carlos@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole(Permissions::ROLE_ACCOUNTING));
        $this->assertTrue(Hash::check($pw, $user->password));
        $this->assertTrue($user->active);
    }

    public function test_weak_password_and_duplicate_email_are_rejected(): void
    {
        $admin = $this->admin(['email' => 'ya@example.com']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'X', 'email' => 'ya@example.com',
            'password' => 'corta', 'password_confirmation' => 'corta',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_unknown_role_is_rejected(): void
    {
        $pw = $this->makePassword();
        $this->actingAs($this->admin())->post(route('admin.users.store'), [
            'name' => 'X', 'email' => 'x@example.com',
            'password' => $pw, 'password_confirmation' => $pw,
            'roles' => ['superpoderes'],
        ])->assertSessionHasErrors('roles.0');
    }

    public function test_admin_updates_user_without_changing_password(): void
    {
        $admin = $this->admin();
        $user = $this->marketer();
        $oldHash = $user->password;

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Nuevo Nombre', 'email' => $user->email,
            'password' => '', 'password_confirmation' => '',
            'roles' => [Permissions::ROLE_MARKETING, Permissions::ROLE_ACCOUNTING], 'active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('Nuevo Nombre', $user->name);
        $this->assertSame($oldHash, $user->password);
        $this->assertTrue($user->hasAllRoles([Permissions::ROLE_MARKETING, Permissions::ROLE_ACCOUNTING]));
    }

    public function test_cannot_remove_admin_role_from_last_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email,
            'roles' => [Permissions::ROLE_MARKETING], 'active' => '1',
        ])->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_can_demote_an_admin_when_another_admin_exists(): void
    {
        $admin = $this->admin();
        $other = $this->admin();

        $this->actingAs($admin)->put(route('admin.users.update', $other), [
            'name' => $other->name, 'email' => $other->email,
            'roles' => [Permissions::ROLE_MARKETING], 'active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($other->fresh()->isAdmin());
    }

    public function test_cannot_deactivate_self(): void
    {
        $admin = $this->admin();
        $this->admin(); // hay otro admin, así que el error es por "propia cuenta"

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email,
            'roles' => [Permissions::ROLE_ADMIN], 'active' => '0',
        ])->assertSessionHasErrors('active');

        $this->assertTrue($admin->fresh()->active);
    }

    public function test_cannot_delete_self_or_last_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors('user');
        $this->assertModelExists($admin);
    }

    public function test_admin_deletes_other_user(): void
    {
        $admin = $this->admin();
        $user = $this->marketer();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users.index'));
        $this->assertModelMissing($user);
    }

    public function test_search_filters_users(): void
    {
        $admin = $this->admin(['name' => 'Ana Admin']);
        User::factory()->create(['name' => 'Pedro Pérez']);

        $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'Pedro']))
            ->assertSee('Pedro Pérez')->assertDontSee('Ana Admin</td>', false);
    }
}

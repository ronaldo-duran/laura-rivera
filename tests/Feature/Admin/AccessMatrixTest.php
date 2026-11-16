<?php

namespace Tests\Feature\Admin;

use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * Quién puede entrar a qué. Si se agrega una sección nueva al panel,
 * agréguela aquí para que la regla quede protegida por pruebas.
 */
class AccessMatrixTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    /** ruta => roles que SÍ pueden entrar (además del administrador) */
    public static function matrix(): array
    {
        return [
            'inicio' => ['admin.dashboard', [Permissions::ROLE_ACCOUNTING, Permissions::ROLE_MARKETING]],
            'usuarios' => ['admin.users.index', []],
            'nuevo usuario' => ['admin.users.create', []],
            'roles' => ['admin.roles.index', []],
            'noticias' => ['admin.news.index', [Permissions::ROLE_MARKETING]],
            'voluntarios' => ['admin.volunteers.index', []],
            'donaciones' => ['admin.donations.index', [Permissions::ROLE_ACCOUNTING]],
            'métricas' => ['admin.metrics.index', [Permissions::ROLE_MARKETING]],
        ];
    }

    #[DataProvider('matrix')]
    public function test_access_by_role(string $routeName, array $allowed): void
    {
        if (! Route::has($routeName)) {
            $this->markTestSkipped("La sección {$routeName} aún no existe en esta rama.");
        }

        $this->actingAs($this->admin())->get(route($routeName))->assertOk();

        foreach ([Permissions::ROLE_ACCOUNTING, Permissions::ROLE_MARKETING] as $role) {
            $response = $this->actingAs($this->userWithRole($role))->get(route($routeName));
            in_array($role, $allowed, true) ? $response->assertOk() : $response->assertForbidden();
        }
    }

    public function test_menu_only_shows_allowed_sections(): void
    {
        $this->actingAs($this->accountant())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Usuarios</a>', false)
            ->assertDontSee('Roles y permisos</a>', false);

        $this->actingAs($this->admin())->get(route('admin.dashboard'))
            ->assertSee('Usuarios</a>', false)
            ->assertSee('Roles y permisos</a>', false);
    }
}

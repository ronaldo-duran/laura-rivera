<?php

namespace Tests\Feature\Admin;

use App\Models\Volunteer;
use App\Support\CsvExport;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class VolunteerAdminTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_admin_lists_and_searches_volunteers(): void
    {
        Volunteer::factory()->create(['first_names' => 'Lucía', 'last_names' => 'Ramírez']);
        Volunteer::factory()->create(['first_names' => 'Pedro', 'last_names' => 'Suárez']);

        $this->actingAs($this->admin())->get(route('admin.volunteers.index', ['q' => 'Lucía']))
            ->assertOk()->assertSee('Lucía Ramírez')->assertDontSee('Pedro Suárez');
    }

    public function test_filter_by_neighborhood(): void
    {
        Volunteer::factory()->create(['first_names' => 'Ana', 'neighborhood' => 'La Parada']);
        Volunteer::factory()->create(['first_names' => 'Beto', 'neighborhood' => 'Lomitas']);

        $this->actingAs($this->admin())->get(route('admin.volunteers.index', ['barrio' => 'Lomitas']))
            ->assertSee('Beto')->assertDontSee('>Ana ', false);
    }

    public function test_detail_page(): void
    {
        $v = Volunteer::factory()->create(['preferred_name' => 'Majo', 'treatment' => 'senora']);

        $this->actingAs($this->admin())->get(route('admin.volunteers.show', $v))
            ->assertOk()->assertSee('Señora Majo')->assertSee($v->document_number);
    }

    public function test_csv_export_has_bom_semicolons_and_neutralized_formulas(): void
    {
        Volunteer::factory()->create(['first_names' => 'Ana', 'preferred_name' => '=HYPERLINK("x")']);

        $response = $this->actingAs($this->admin())->get(route('admin.volunteers.export'));
        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('"Número de documento";', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(';=HYPERLINK', $csv);
    }

    public function test_csv_safe_helper(): void
    {
        $this->assertSame("'=1+1", CsvExport::safe('=1+1'));
        $this->assertSame("'+57", CsvExport::safe('+57'));
        $this->assertSame('Ana', CsvExport::safe('Ana'));
    }

    public function test_export_requires_export_permission(): void
    {
        $role = Role::findOrCreate('coordinación', 'web');
        $this->seedRoles();
        $role->syncPermissions([Permissions::DASHBOARD_VIEW, Permissions::VOLUNTEERS_VIEW]);
        $user = \App\Models\User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)->get(route('admin.volunteers.index'))->assertOk()->assertDontSee('Exportar CSV');
        $this->actingAs($user)->get(route('admin.volunteers.export'))->assertForbidden();
    }

    public function test_delete_requires_permission_and_removes_data(): void
    {
        $v = Volunteer::factory()->create();

        $role = Role::findOrCreate('coordinación', 'web');
        $this->seedRoles();
        $role->syncPermissions([Permissions::DASHBOARD_VIEW, Permissions::VOLUNTEERS_VIEW]);
        $coordinator = \App\Models\User::factory()->create();
        $coordinator->assignRole($role);

        $this->actingAs($coordinator)->delete(route('admin.volunteers.destroy', $v))->assertForbidden();
        $this->assertModelExists($v);

        $this->actingAs($this->admin())->delete(route('admin.volunteers.destroy', $v))
            ->assertRedirect(route('admin.volunteers.index'));
        $this->assertModelMissing($v);
    }

    public function test_dashboard_shows_volunteer_count_to_admin_only(): void
    {
        Volunteer::factory()->count(3)->create();

        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertSee('Voluntarios registrados');
        $this->actingAs($this->marketer())->get(route('admin.dashboard'))->assertDontSee('Voluntarios registrados');
    }
}

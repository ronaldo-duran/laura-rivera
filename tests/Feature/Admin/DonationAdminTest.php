<?php

namespace Tests\Feature\Admin;

use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class DonationAdminTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_accountant_sees_donations_and_approved_total(): void
    {
        Donation::factory()->approved()->create(['amount_in_cents' => 5000000, 'full_name' => 'Ana Aprobada']);
        Donation::factory()->approved()->create(['amount_in_cents' => 2000000]);
        Donation::factory()->create(['amount_in_cents' => 9900000, 'full_name' => 'Pepe Pendiente']);

        $this->actingAs($this->accountant())->get(route('admin.donations.index'))
            ->assertOk()
            ->assertSee('Ana Aprobada')
            ->assertSee('Pepe Pendiente')
            ->assertSee('$70.000');
    }

    public function test_filter_by_status_and_date(): void
    {
        Donation::factory()->approved()->create(['full_name' => 'Ana Aprobada', 'created_at' => '2026-11-10 10:00:00']);
        Donation::factory()->create(['full_name' => 'Pepe Pendiente', 'created_at' => '2026-11-15 10:00:00']);

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.donations.index', ['estado' => 'APPROVED']))
            ->assertSee('Ana Aprobada')->assertDontSee('Pepe Pendiente');
        $this->actingAs($admin)->get(route('admin.donations.index', ['desde' => '2026-11-14']))
            ->assertSee('Pepe Pendiente')->assertDontSee('Ana Aprobada');
        $this->actingAs($admin)->get(route('admin.donations.index', ['desde' => 'basura']))->assertOk();
    }

    public function test_export_csv_for_accountant(): void
    {
        Donation::factory()->approved()->create(['amount_in_cents' => 5000000, 'reference' => 'LR-EXP-1']);

        $csv = $this->actingAs($this->accountant())->get(route('admin.donations.export'))
            ->assertOk()->streamedContent();

        $this->assertStringContainsString('LR-EXP-1', $csv);
        $this->assertStringContainsString('Aprobada;50000', $csv);
    }

    public function test_detail_page(): void
    {
        $d = Donation::factory()->approved()->create();

        $this->actingAs($this->accountant())->get(route('admin.donations.show', $d))
            ->assertOk()->assertSee($d->reference)->assertSee('Aprobada');
    }

    public function test_marketing_cannot_see_donations(): void
    {
        $this->actingAs($this->marketer())->get(route('admin.donations.index'))->assertForbidden();
        $this->actingAs($this->marketer())->get(route('admin.dashboard'))->assertDontSee('Donaciones aprobadas');
    }

    public function test_dashboard_card_for_accountant(): void
    {
        Donation::factory()->approved()->create(['amount_in_cents' => 5000000]);

        $this->actingAs($this->accountant())->get(route('admin.dashboard'))
            ->assertSee('Donaciones aprobadas')->assertSee('$50.000');
    }
}

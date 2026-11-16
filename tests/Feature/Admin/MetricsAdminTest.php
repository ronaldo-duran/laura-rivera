<?php

namespace Tests\Feature\Admin;

use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class MetricsAdminTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private function seedVisits(): void
    {
        Visit::factory()->count(3)->fromAi('ChatGPT')->create(['path' => '/quien-es-laura']);
        Visit::factory()->fromAi('Claude')->create(['ip' => '181.50.1.2']);
        Visit::factory()->create(['channel' => 'buscador', 'source_name' => 'Google']);
        Visit::factory()->create(['is_entry' => false, 'channel' => 'interno']);
        Visit::factory()->bot('ClaudeBot (Anthropic)')->create(['path' => '/llms.txt']);
        Visit::factory()->fromAi('Gemini')->create(['created_at' => now()->subDays(60)]);
        VisitEvent::create(['type' => 'click', 'label' => 'Quiero ser voluntario', 'path' => '/']);
        VisitEvent::create(['type' => 'engagement', 'value' => 90, 'path' => '/']);
    }

    public function test_marketing_sees_ai_sources_crawlers_and_clicks(): void
    {
        $this->seedVisits();

        $this->actingAs($this->marketer())->get(route('admin.metrics.index'))
            ->assertOk()
            ->assertSee('Llegaron por IA')
            ->assertSee('ChatGPT')
            ->assertSee('Claude')
            ->assertDontSee('Gemini</span>', false)          // fuera del periodo de 30 días
            ->assertSee('/quien-es-laura')
            ->assertSee('ClaudeBot (Anthropic)')
            ->assertSee('/llms.txt')
            ->assertSee('Quiero ser voluntario')
            ->assertSee('181.50.1.2')
            ->assertSee('01:30');                              // tiempo medio en página
    }

    public function test_totals_are_correct(): void
    {
        $this->seedVisits();
        $t = \App\Support\Analytics\MetricsReport::lastDays(30)->totals();

        $this->assertSame(6, $t['pageviews']);
        $this->assertSame(5, $t['sessions']);
        $this->assertSame(4, $t['ai_entries']);
        $this->assertSame(1, $t['search_entries']);
        $this->assertSame(1, $t['ai_bot_hits']);
        $this->assertSame(1, $t['clicks']);
        $this->assertSame(90, $t['avg_seconds']);

        $this->assertSame(30, \App\Support\Analytics\MetricsReport::lastDays(30)->daily()->count());
        $this->assertSame(7, \App\Support\Analytics\MetricsReport::lastDays(90)->totals()['sessions'] + 1);
    }

    public function test_ranges_and_empty_state(): void
    {
        $admin = $this->admin();
        foreach ([1, 7, 30, 90, 365, 999] as $d) {
            $this->actingAs($admin)->get(route('admin.metrics.index', ['dias' => $d]))->assertOk();
        }
        $this->actingAs($admin)->get(route('admin.metrics.index'))->assertSee('Sin datos en este periodo');
    }

    public function test_visit_list_filters_and_export(): void
    {
        $this->seedVisits();
        $user = $this->marketer();

        $this->actingAs($user)->get(route('admin.metrics.visits', ['ip' => '181.50.1.2']))
            ->assertOk()->assertSee('181.50.1.2')->assertSee('Claude');
        $this->actingAs($user)->get(route('admin.metrics.visits'))->assertDontSee('ClaudeBot');
        $this->actingAs($user)->get(route('admin.metrics.visits', ['bots' => 1]))->assertSee('ClaudeBot');

        $csv = $this->actingAs($user)->get(route('admin.metrics.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('181.50.1.2', $csv);
        $this->assertStringContainsString('Asistentes de IA', $csv);
    }

    public function test_dashboard_card_for_marketing_only(): void
    {
        $this->seedVisits();

        $this->actingAs($this->marketer())->get(route('admin.dashboard'))->assertSee('Visitas (7 días)')->assertSee('4 llegaron por IA');
        $this->actingAs($this->accountant())->get(route('admin.dashboard'))->assertDontSee('Visitas (7 días)');
        $this->actingAs($this->accountant())->get(route('admin.metrics.index'))->assertForbidden();
    }

    public function test_prune_command_respects_retention(): void
    {
        Visit::factory()->create(['created_at' => now()->subDays(500)]);
        Visit::factory()->create();
        VisitEvent::create(['type' => 'click', 'created_at' => now()->subDays(500)]);

        $this->artisan('app:podar-metricas')->assertSuccessful();

        $this->assertSame(1, Visit::count());
        $this->assertSame(0, VisitEvent::count());
        $this->artisan('app:podar-metricas', ['--dias' => 0])->assertFailed();
    }
}

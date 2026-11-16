<?php

namespace Tests\Feature\Metrics;

use App\Models\VisitEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCollectorTest extends TestCase
{
    use RefreshDatabase;

    private function beacon(array $events, ?string $consent = 'all', string $ua = 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0 Safari/537.36')
    {
        // Igual que sendBeacon: cuerpo JSON sin token CSRF, con las cookies del navegador
        return $this->call('POST', '/metricas/evento', [], $consent ? ['lr_consent' => $consent] : [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_USER_AGENT' => $ua], json_encode(['events' => $events]));
    }

    public function test_clicks_are_stored_with_consent(): void
    {
        $this->beacon([
            ['type' => 'click', 'label' => "  Quiero   ser\n voluntario ", 'target' => '/voluntarios', 'path' => '/'],
            ['type' => 'scroll', 'label' => '50%', 'path' => '/', 'value' => 50],
        ])->assertNoContent();

        $this->assertSame(2, VisitEvent::count());
        $click = VisitEvent::where('type', 'click')->sole();
        $this->assertSame('Quiero ser voluntario', $click->label);
        $this->assertSame('/voluntarios', $click->target);
    }

    public function test_nothing_is_stored_without_consent_or_for_bots(): void
    {
        $this->beacon([['type' => 'click', 'label' => 'x']], null)->assertNoContent();
        $this->beacon([['type' => 'click', 'label' => 'x']], 'essential')->assertNoContent();
        $this->beacon([['type' => 'click', 'label' => 'x']], 'all', 'curl/8.0')->assertNoContent();

        $this->assertSame(0, VisitEvent::count());
    }

    public function test_invalid_events_are_ignored_and_batch_is_capped(): void
    {
        $this->beacon([
            ['type' => 'hack', 'label' => 'x'],
            ['type' => 'engagement', 'value' => -5],
            'no-es-un-arreglo',
            ['type' => 'engagement', 'value' => 42],
        ])->assertNoContent();
        $this->assertSame(1, VisitEvent::count());

        $this->beacon(array_fill(0, 50, ['type' => 'click', 'label' => 'a']));
        $this->assertSame(21, VisitEvent::count());
    }

    public function test_malformed_body_does_not_fail(): void
    {
        $this->call('POST', '/metricas/evento', [], ['lr_consent' => 'all'], [], ['CONTENT_TYPE' => 'application/json'], '{roto')
            ->assertNoContent();
    }

    public function test_long_values_are_truncated(): void
    {
        $this->beacon([['type' => 'outbound', 'label' => str_repeat('a', 400), 'target' => 'https://x.com/'.str_repeat('b', 900)]]);

        $e = VisitEvent::sole();
        $this->assertSame(150, mb_strlen($e->label));
        $this->assertSame(500, mb_strlen($e->target));
    }
}

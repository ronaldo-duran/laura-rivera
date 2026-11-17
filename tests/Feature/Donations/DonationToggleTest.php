<?php

namespace Tests\Feature\Donations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\FakesWompi;
use Tests\TestCase;

/**
 * Interruptor general de donaciones (config laura.donaciones.activas / env DONATIONS_ENABLED).
 * Apagado: la sección se oculta del sitio y del sitemap y las rutas /donar responden 404.
 * El webhook de Wompi sigue registrado en ambos casos.
 */
class DonationToggleTest extends TestCase
{
    use FakesWompi, RefreshDatabase;

    private function disable(): void
    {
        config(['laura.donaciones.activas' => false]);
    }

    private function enable(): void
    {
        config(['laura.donaciones.activas' => true]);
        $this->configureWompi();
    }

    public function test_disabled_hides_donations_from_navigation_and_home(): void
    {
        $this->disable();

        $home = $this->get('/')->assertOk();
        $home->assertDontSee('Hacer un aporte');
        $home->assertDontSee('>Donar<', false);         // botón del menú
    }

    public function test_disabled_routes_return_404(): void
    {
        $this->disable();

        $this->get('/donar')->assertNotFound();
        $this->get('/donar/resultado')->assertNotFound();
        $this->post('/donar', [])->assertNotFound();
    }

    public function test_disabled_sitemap_excludes_donations(): void
    {
        $this->disable();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringNotContainsString(route('donations.create'), $xml);
    }

    public function test_enabled_shows_donations_and_lists_them_in_sitemap(): void
    {
        $this->enable();

        $this->get('/donar')->assertOk();
        $this->get('/')->assertOk()->assertSee('Hacer un aporte');

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString(route('donations.create'), $xml);
    }

    public function test_webhook_route_stays_registered_when_disabled(): void
    {
        $this->disable();

        // El endpoint del webhook sigue existiendo (no es 404) aunque las donaciones estén ocultas.
        $this->assertTrue(Route::has('webhooks.wompi'));
        $this->assertNotSame(404, $this->post('/webhooks/wompi', [])->getStatusCode());
    }
}

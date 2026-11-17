<?php

namespace Tests\Feature\PublicSite;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function publicRoutes(): array
    {
        return [
            'inicio' => ['/'],
            'quién es laura' => ['/quien-es-laura'],
            'visión del municipio' => ['/vision-del-municipio'],
            'noticias' => ['/noticias'],
            'términos' => ['/terminos-y-condiciones'],
            'privacidad' => ['/politica-de-privacidad'],
        ];
    }

    #[DataProvider('publicRoutes')]
    public function test_public_pages_load(string $uri): void
    {
        $this->get($uri)
            ->assertOk()
            ->assertSee('Laura Rivera')
            ->assertSee('data-cookie-banner', false)
            ->assertSee('<link rel="canonical"', false);
    }

    #[DataProvider('publicRoutes')]
    public function test_pages_never_call_her_a_candidate(string $uri): void
    {
        // Norma: no se puede presentar como candidata hasta que sea oficial.
        $html = mb_strtolower($this->get($uri)->getContent());

        $this->assertStringNotContainsString('candidata', $html);
        $this->assertStringNotContainsString('candidato', $html);
        $this->assertStringNotContainsString('2028', $html);
    }

    public function test_home_presents_her_as_empresaria(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Empresaria')
            ->assertSee('Laura Milagros Rivera Rodríguez');
    }

    public function test_home_has_person_structured_data(): void
    {
        $html = $this->get('/')->getContent();

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'No se encontró JSON-LD');

        $data = json_decode($m[1], true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());

        $person = collect($data['@graph'])->firstWhere('@type', 'Person');
        $this->assertSame('Laura Milagros Rivera Rodríguez', $person['name']);
        $this->assertSame('Empresaria y Gerente de Proyectos', $person['jobTitle']);
    }

    public function test_all_proposal_axes_are_rendered_in_html(): void
    {
        $response = $this->get('/vision-del-municipio')->assertOk();

        foreach (config('laura.ejes') as $eje) {
            $response->assertSee($eje['nombre']);
            foreach ($eje['items'] as $item) {
                $response->assertSee($item);
            }
        }
    }

    public function test_vision_section_replaces_propuestas_and_old_slug_redirects(): void
    {
        // La sección ahora se llama "Visión del municipio" (tono neutral, sin campaña).
        $this->get('/vision-del-municipio')->assertOk()->assertSee('Visión del municipio');
        $this->get('/')->assertOk()->assertSee('Visión del municipio')->assertDontSee('>Propuestas<', false);

        // El slug anterior redirige de forma permanente.
        $this->get('/propuestas')->assertRedirect('/vision-del-municipio');
        $this->assertSame(301, $this->get('/propuestas')->getStatusCode());
    }

    public function test_about_page_contains_cv(): void
    {
        $response = $this->get('/quien-es-laura')->assertOk();

        foreach (config('laura.experiencia') as $c) {
            $response->assertSee($c['org']);
        }
        foreach (config('laura.formacion') as $e) {
            $response->assertSee($e['titulo']);
        }
    }

    public function test_home_shows_latest_three_published_news(): void
    {
        News::factory()->count(4)->sequence(
            ['title' => 'Noticia uno', 'published_at' => now()->subDays(4)],
            ['title' => 'Noticia dos', 'published_at' => now()->subDays(3)],
            ['title' => 'Noticia tres', 'published_at' => now()->subDays(2)],
            ['title' => 'Noticia cuatro', 'published_at' => now()->subDay()],
        )->create();
        News::factory()->draft()->create(['title' => 'Borrador secreto']);

        $this->get('/')
            ->assertSee('Noticia cuatro')
            ->assertSee('Noticia dos')
            ->assertDontSee('Noticia uno')
            ->assertDontSee('Borrador secreto');
    }
}

<?php

namespace Tests\Feature\PublicSite;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_valid_xml_and_lists_pages_and_news(): void
    {
        $news = News::factory()->create();
        $draft = News::factory()->draft()->create();

        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'El sitemap no es XML válido');

        $locs = collect();
        foreach ($xml->url as $u) {
            $locs->push((string) $u->loc);
        }
        $this->assertTrue($locs->contains(route('home')));
        $this->assertTrue($locs->contains(route('about')));
        $this->assertTrue($locs->contains(route('news.show', $news)));
        $this->assertFalse($locs->contains(route('news.show', $draft)));
    }

    public function test_robots_allows_ai_crawlers_and_blocks_admin(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();

        foreach (['GPTBot', 'ClaudeBot', 'Google-Extended', 'PerplexityBot'] as $bot) {
            $this->assertStringContainsString("User-agent: {$bot}", $body);
        }
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Sitemap: '.route('sitemap'), $body);
    }

    public function test_llms_txt_contains_factual_bio_and_unescaped_text(): void
    {
        News::factory()->create(['title' => 'Encuentro "Rosario Seguro"']);

        $response = $this->get('/llms.txt')->assertOk();
        $body = $response->getContent();

        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('# Laura Milagros Rivera Rodríguez', $body);
        $this->assertStringContainsString('Secretaria de Vivienda y Ambiente', $body);
        $this->assertStringContainsString('Encuentro "Rosario Seguro"', $body);
        $this->assertStringNotContainsString('&quot;', $body);
        $this->assertStringNotContainsStringIgnoringCase('candidata', $body);
    }

    public function test_pages_have_unique_titles_and_descriptions(): void
    {
        $titles = [];
        foreach (['/', '/quien-es-laura', '/vision-del-municipio', '/noticias'] as $uri) {
            $html = $this->get($uri)->getContent();
            preg_match('/<title>(.*?)<\/title>/s', $html, $t);
            preg_match('/<meta name="description" content="(.*?)">/s', $html, $d);
            $this->assertNotEmpty($t[1] ?? null, "Sin <title> en {$uri}");
            $this->assertNotEmpty($d[1] ?? null, "Sin description en {$uri}");
            $titles[] = $t[1];
        }

        $this->assertSame(count($titles), count(array_unique($titles)));
    }
}

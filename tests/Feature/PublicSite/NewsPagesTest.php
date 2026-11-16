<?php

namespace Tests\Feature\PublicSite;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_news_detail_is_visible(): void
    {
        $news = News::factory()->create([
            'title' => 'Jornada de vivienda en Lomitas',
            'body' => "Primer párrafo.\n\nSegundo párrafo.",
        ]);

        $this->get(route('news.show', $news))
            ->assertOk()
            ->assertSee('Jornada de vivienda en Lomitas')
            ->assertSee('Segundo párrafo.')
            ->assertSee('"@type":"NewsArticle"', false);
    }

    public function test_draft_news_returns_404(): void
    {
        $news = News::factory()->draft()->create();

        $this->get(route('news.show', $news))->assertNotFound();
    }

    public function test_scheduled_news_returns_404_until_published(): void
    {
        $news = News::factory()->create(['published_at' => now()->addDay()]);

        $this->get(route('news.show', $news))->assertNotFound();
        $this->get('/noticias')->assertDontSee($news->title);
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/noticias/no-existe')->assertNotFound();
    }

    public function test_news_index_paginates(): void
    {
        News::factory()->count(12)->create();

        $this->get('/noticias')->assertOk()->assertSee('Página 1 de 2');
        $this->get('/noticias?page=2')->assertOk()->assertSee('Página 2 de 2');
    }

    public function test_news_body_is_escaped(): void
    {
        $news = News::factory()->create(['body' => '<script>alert(1)</script>']);

        $this->get(route('news.show', $news))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_from_title(): void
    {
        $news = News::create(['title' => 'Gran Encuentro en el Templo Histórico', 'published' => true]);

        $this->assertSame('gran-encuentro-en-el-templo-historico', $news->slug);
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $a = News::create(['title' => 'Recorrido', 'published' => true]);
        $b = News::create(['title' => 'Recorrido', 'published' => true]);

        $this->assertSame('recorrido', $a->slug);
        $this->assertSame('recorrido-2', $b->slug);
    }

    public function test_published_at_is_set_when_publishing(): void
    {
        $news = News::create(['title' => 'Sin fecha', 'published' => true]);

        $this->assertNotNull($news->published_at);
    }

    public function test_published_scope_excludes_drafts_and_future(): void
    {
        News::factory()->create(['title' => 'Visible']);
        News::factory()->draft()->create(['title' => 'Borrador']);
        News::factory()->create(['title' => 'Futura', 'published_at' => now()->addWeek()]);

        $this->assertSame(['Visible'], News::published()->pluck('title')->all());
    }
}

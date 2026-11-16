<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class NewsAdminTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Recorrido por el barrio Centro',
            'tag' => 'Recorrido',
            'summary' => 'Laura conversó con comerciantes del sector.',
            'body' => "Primer párrafo.\n\nSegundo párrafo.",
            'published' => '1',
        ], $overrides);
    }

    public function test_marketing_creates_news_with_image_and_it_appears_on_the_site(): void
    {
        $user = $this->marketer();

        $this->actingAs($user)->post(route('admin.news.store'), $this->data([
            'image' => UploadedFile::fake()->image('foto.jpg', 1200, 750),
            'image_caption' => 'Laura con comerciantes',
        ]))->assertRedirect(route('admin.news.index'));

        $news = News::sole();
        $this->assertSame('recorrido-por-el-barrio-centro', $news->slug);
        $this->assertSame($user->id, $news->author_id);
        $this->assertNotNull($news->published_at);
        Storage::disk('public')->assertExists($news->image_path);
        $this->assertStringStartsWith('noticias/', $news->image_path);

        auth()->logout();
        $this->get(route('news.show', $news))->assertOk()->assertSee('Recorrido por el barrio Centro')->assertSee('storage/'.$news->image_path);
        $this->get('/noticias')->assertSee('Recorrido por el barrio Centro');
    }

    public function test_draft_and_scheduled_news_are_not_public(): void
    {
        $user = $this->marketer();
        $this->actingAs($user)->post(route('admin.news.store'), $this->data(['title' => 'Borrador secreto', 'published' => '0']));
        $this->actingAs($user)->post(route('admin.news.store'), $this->data(['title' => 'Programada', 'published_at' => now()->addDays(3)->format('Y-m-d\TH:i')]));

        $this->actingAs($user)->get(route('admin.news.index'))->assertSee('Borrador')->assertSee('Programada');

        auth()->logout();
        $this->get('/noticias')->assertDontSee('Borrador secreto')->assertDontSee('Programada');
        $this->get('/noticias/borrador-secreto')->assertNotFound();
    }

    public function test_update_replaces_and_removes_image(): void
    {
        $user = $this->marketer();
        $this->actingAs($user)->post(route('admin.news.store'), $this->data(['image' => UploadedFile::fake()->image('a.jpg')]));
        $news = News::sole();
        $old = $news->image_path;

        $this->actingAs($user)->put(route('admin.news.update', $news), $this->data([
            'title' => 'Título nuevo', 'image' => UploadedFile::fake()->image('b.png'),
        ]))->assertRedirect();
        $news->refresh();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($news->image_path);
        $this->assertSame('Título nuevo', $news->title);
        $this->assertSame('recorrido-por-el-barrio-centro', $news->slug, 'el enlace público no cambia al editar');

        $current = $news->image_path;
        $this->actingAs($user)->put(route('admin.news.update', $news), $this->data(['remove_image' => '1']));
        $this->assertNull($news->fresh()->image_path);
        Storage::disk('public')->assertMissing($current);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->marketer())->post(route('admin.news.store'), $this->data([
            'title' => '', 'body' => '', 'image' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ]))->assertSessionHasErrors(['title', 'body', 'image']);

        $this->assertSame(0, News::count());
    }

    public function test_delete_removes_news_and_its_image(): void
    {
        $user = $this->marketer();
        $this->actingAs($user)->post(route('admin.news.store'), $this->data(['image' => UploadedFile::fake()->image('a.jpg')]));
        $news = News::sole();

        $this->actingAs($user)->delete(route('admin.news.destroy', $news))->assertRedirect(route('admin.news.index'));

        $this->assertSame(0, News::count());
        Storage::disk('public')->assertMissing($news->image_path);
    }

    public function test_accounting_cannot_manage_news(): void
    {
        $news = News::factory()->create();
        $user = $this->accountant();

        $this->actingAs($user)->get(route('admin.news.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.news.store'), $this->data())->assertForbidden();
        $this->actingAs($user)->delete(route('admin.news.destroy', $news))->assertForbidden();
        $this->assertSame(1, News::count());
    }

    public function test_edit_form_shows_current_values(): void
    {
        $news = News::factory()->create(['title' => 'Una noticia existente']);

        $this->actingAs($this->marketer())->get(route('admin.news.edit', $news))
            ->assertOk()->assertSee('Una noticia existente')->assertSee('candidata');
    }
}

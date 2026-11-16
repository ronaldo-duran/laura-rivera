<?php

namespace Tests\Feature\Metrics;

use App\Models\Visit;
use App\Models\Volunteer;
use App\Support\Analytics\SourceClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class VisitTrackingTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36';

    private function visit(string $uri, array $headers = [])
    {
        return $this->withHeaders(array_merge(['User-Agent' => self::BROWSER], $headers))->get($uri);
    }

    public function test_page_view_is_recorded_with_source_device_and_ip(): void
    {
        $this->visit('/propuestas', ['Referer' => 'https://chatgpt.com/', 'Accept-Language' => 'es-CO,es;q=0.9'])->assertOk();

        $v = Visit::sole();
        $this->assertSame('/propuestas', $v->path);
        $this->assertSame('proposals', $v->route);
        $this->assertSame(SourceClassifier::AI, $v->channel);
        $this->assertSame('ChatGPT', $v->source_name);
        $this->assertSame('chatgpt.com', $v->referrer_host);
        $this->assertSame('127.0.0.1', $v->ip);
        $this->assertSame('movil', $v->device);
        $this->assertSame('Chrome', $v->browser);
        $this->assertTrue($v->is_entry);
        $this->assertFalse($v->is_bot);
        $this->assertNotNull($v->session_hash);
    }

    public function test_utm_parameters_are_recorded(): void
    {
        $this->visit('/?utm_source=chatgpt.com')->assertOk();
        $this->assertSame(['ia', 'ChatGPT'], [Visit::sole()->channel, Visit::sole()->source_name]);

        Visit::query()->delete();
        $this->flushSession();
        $this->visit('/?utm_source=facebook&utm_medium=cpc&utm_campaign=lanzamiento')->assertOk();
        $v = Visit::sole();
        $this->assertSame(SourceClassifier::CAMPAIGN, $v->channel);
        $this->assertSame(['facebook', 'cpc', 'lanzamiento'], [$v->utm_source, $v->utm_medium, $v->utm_campaign]);
    }

    public function test_internal_navigation_is_not_a_new_entry(): void
    {
        $this->visit('/', ['Referer' => 'https://www.google.com/']);
        $this->visit('/quien-es-laura', ['Referer' => 'http://localhost/']);

        [$first, $second] = Visit::orderBy('id')->get()->all();
        $this->assertTrue($first->is_entry);
        $this->assertSame(SourceClassifier::SEARCH, $first->channel);
        $this->assertFalse($second->is_entry);
        $this->assertSame(SourceClassifier::INTERNAL, $second->channel);
    }

    public function test_first_source_is_saved_with_volunteer_registration(): void
    {
        $this->visit('/', ['Referer' => 'https://claude.ai/']);
        $this->visit('/voluntarios', ['Referer' => 'http://localhost/']);

        $this->post('/voluntarios', [
            'document_type' => 'CC', 'document_number' => '1090123456', 'first_names' => 'Ana', 'last_names' => 'Pérez',
            'treatment' => 'senora', 'email' => 'ana@example.com', 'phone' => '3001234567', 'accept_terms' => '1',
        ])->assertRedirect();

        $this->assertSame('Asistentes de IA · Claude', Volunteer::sole()->source);
    }

    public function test_ai_crawlers_are_recorded_as_bots(): void
    {
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; GPTBot/1.2; +https://openai.com/gptbot)'])->get('/llms.txt')->assertOk();

        $v = Visit::sole();
        $this->assertTrue($v->is_bot);
        $this->assertTrue($v->is_ai_bot);
        $this->assertSame('GPTBot (OpenAI)', $v->bot_name);
        $this->assertSame(SourceClassifier::BOT, $v->channel);
        $this->assertSame('/llms.txt', $v->path);
    }

    public function test_not_found_pages_are_recorded_with_status(): void
    {
        $this->visit('/no-existe')->assertNotFound();
        $this->assertSame(404, Visit::sole()->status);
    }

    public function test_admin_login_webhooks_posts_and_logged_in_team_are_not_tracked(): void
    {
        $this->visit('/login');
        $this->withHeaders(['User-Agent' => self::BROWSER])->post('/voluntarios', []);
        $this->withHeaders(['Sec-Purpose' => 'prefetch'])->get('/');

        $this->actingAs($this->admin());
        $this->visit('/');
        $this->visit('/admin');

        $this->assertSame(0, Visit::count());
    }

    public function test_tracking_can_be_disabled(): void
    {
        config(['laura.metricas.activas' => false]);
        $this->visit('/')->assertOk();
        $this->assertSame(0, Visit::count());
    }

    public function test_tracking_failure_never_breaks_the_page(): void
    {
        \Illuminate\Support\Facades\Schema::drop('visits');

        $this->visit('/')->assertOk();
    }

    public function test_visitor_id_cookie_is_read_without_encryption(): void
    {
        $this->withUnencryptedCookie('lr_vid', 'abcd1234-ef56')->visit('/')->assertOk();
        $this->assertSame('abcd1234-ef56', Visit::sole()->visitor_id);

        Visit::query()->delete();
        $this->withUnencryptedCookie('lr_vid', '<script>')->visit('/');
        $this->assertNull(Visit::sole()->visitor_id);
    }

    public function test_pages_include_metrics_endpoint(): void
    {
        $this->visit('/')->assertSee('<meta name="lr-metrics" content="'.route('metrics.event', absolute: false).'">', false);
    }
}

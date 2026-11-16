<?php

namespace App\Support\Analytics;

use App\Models\Donation;
use App\Models\Visit;
use App\Models\VisitEvent;
use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Consultas agregadas para la página de métricas del panel. Solo usa SQL
 * estándar (COUNT, DATE, GROUP BY) para funcionar igual en MariaDB y SQLite.
 */
class MetricsReport
{
    public function __construct(public readonly Carbon $from, public readonly Carbon $to) {}

    public static function lastDays(int $days): self
    {
        return new self(now()->subDays($days - 1)->startOfDay(), now()->endOfDay());
    }

    private function visits(): Builder
    {
        return Visit::query()->whereBetween('created_at', [$this->from, $this->to]);
    }

    private function humans(): Builder
    {
        return $this->visits()->where('is_bot', false);
    }

    private function entries(): Builder
    {
        return $this->humans()->where('is_entry', true);
    }

    private function events(): Builder
    {
        return VisitEvent::query()->whereBetween('created_at', [$this->from, $this->to]);
    }

    /** @return array<string, int|float|null> */
    public function totals(): array
    {
        $engagement = $this->events()->where('type', 'engagement')->avg('value');

        return [
            'pageviews' => $this->humans()->where('status', 200)->count(),
            'sessions' => $this->entries()->count(),
            'visitors' => $this->humans()->whereNotNull('ip')->distinct()->count('ip'),
            'ai_entries' => $this->entries()->where('channel', SourceClassifier::AI)->count(),
            'search_entries' => $this->entries()->where('channel', SourceClassifier::SEARCH)->count(),
            'clicks' => $this->events()->whereIn('type', ['click', 'outbound', 'whatsapp', 'tel', 'mailto', 'download'])->count(),
            'avg_seconds' => $engagement !== null ? (int) round($engagement) : null,
            'bot_hits' => $this->visits()->where('is_bot', true)->count(),
            'ai_bot_hits' => $this->visits()->where('is_ai_bot', true)->count(),
            'volunteers' => Volunteer::whereBetween('created_at', [$this->from, $this->to])->count(),
            'donations' => Donation::approved()->whereBetween('created_at', [$this->from, $this->to])->count(),
        ];
    }

    /** Serie diaria: páginas vistas y sesiones. */
    public function daily(): Collection
    {
        $rows = $this->humans()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as views, SUM(CASE WHEN is_entry THEN 1 ELSE 0 END) as sessions, SUM(CASE WHEN is_entry AND channel = ? THEN 1 ELSE 0 END) as ai', [SourceClassifier::AI])
            ->groupBy('day')->get()->keyBy(fn ($r) => (string) $r->day);

        $out = collect();
        for ($d = $this->from->copy()->startOfDay(); $d->lte($this->to); $d->addDay()) {
            $r = $rows->get($d->toDateString());
            $out->push(['day' => $d->copy(), 'views' => (int) ($r->views ?? 0), 'sessions' => (int) ($r->sessions ?? 0), 'ai' => (int) ($r->ai ?? 0)]);
        }

        return $out;
    }

    public function channels(): Collection
    {
        return $this->group($this->entries(), 'channel', 20)
            ->map(fn ($r) => (object) ['label' => SourceClassifier::LABELS[$r->key] ?? $r->key, 'key' => $r->key, 'total' => $r->total]);
    }

    public function aiAssistants(): Collection
    {
        return $this->group($this->entries()->where('channel', SourceClassifier::AI), 'source_name');
    }

    public function aiLandingPages(): Collection
    {
        return $this->group($this->entries()->where('channel', SourceClassifier::AI), 'path');
    }

    public function sources(): Collection
    {
        return $this->entries()
            ->whereNotIn('channel', [SourceClassifier::DIRECT, SourceClassifier::INTERNAL])
            ->selectRaw('channel, source_name, COUNT(*) as total')
            ->groupBy('channel', 'source_name')->orderByDesc('total')->limit(15)->get();
    }

    public function topPages(): Collection
    {
        return $this->group($this->humans()->where('status', 200), 'path', 12);
    }

    public function notFound(): Collection
    {
        return $this->group($this->humans()->where('status', 404), 'path', 8);
    }

    public function campaigns(): Collection
    {
        return $this->entries()->whereNotNull('utm_source')
            ->selectRaw('utm_source, utm_medium, utm_campaign, COUNT(*) as total')
            ->groupBy('utm_source', 'utm_medium', 'utm_campaign')->orderByDesc('total')->limit(10)->get();
    }

    public function breakdown(string $column): Collection
    {
        return $this->group($this->entries(), $column, 8);
    }

    public function topIps(): Collection
    {
        return $this->humans()->whereNotNull('ip')
            ->selectRaw('ip, COUNT(*) as total, SUM(CASE WHEN is_entry THEN 1 ELSE 0 END) as sessions, MAX(created_at) as last_seen')
            ->groupBy('ip')->orderByDesc('total')->limit(10)->get();
    }

    public function clicks(): Collection
    {
        return $this->events()->whereIn('type', ['click', 'outbound', 'whatsapp', 'tel', 'mailto', 'download'])
            ->selectRaw('type, label, COUNT(*) as total')
            ->groupBy('type', 'label')->orderByDesc('total')->limit(15)->get();
    }

    public function scroll(): Collection
    {
        return $this->group($this->events()->where('type', 'scroll'), 'label', 5);
    }

    public function bots(): Collection
    {
        return $this->visits()->where('is_bot', true)
            ->selectRaw('bot_name, is_ai_bot, COUNT(*) as total, MAX(created_at) as last_seen')
            ->groupBy('bot_name', 'is_ai_bot')->orderByDesc('total')->limit(15)->get();
    }

    public function aiBotPages(): Collection
    {
        return $this->group($this->visits()->where('is_ai_bot', true), 'path', 8);
    }

    public function volunteersBySource(): Collection
    {
        return $this->group(Volunteer::query()->whereBetween('created_at', [$this->from, $this->to]), 'source', 8);
    }

    private function group(Builder $query, string $column, int $limit = 10): Collection
    {
        $col = DB::getQueryGrammar()->wrap($column);

        return $query->selectRaw("{$col} as grp_key, COUNT(*) as total")
            ->groupBy($column)->orderByDesc('total')->limit($limit)->get()
            ->map(fn ($r) => (object) ['key' => $r->grp_key, 'total' => (int) $r->total]);
    }
}

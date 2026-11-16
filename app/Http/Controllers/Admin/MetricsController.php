<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Support\Analytics\MetricsReport;
use App\Support\Analytics\SourceClassifier;
use App\Support\CsvExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MetricsController extends Controller
{
    public const RANGES = [1 => 'Hoy', 7 => '7 días', 30 => '30 días', 90 => '90 días', 365 => '12 meses'];

    public function index(Request $request): View
    {
        $days = $this->days($request);

        return view('admin.metrics.index', [
            'r' => MetricsReport::lastDays($days),
            'days' => $days,
            'ranges' => self::RANGES,
        ]);
    }

    public function visits(Request $request): View
    {
        return view('admin.metrics.visits', [
            'visits' => $this->filtered($request)->latest('id')->paginate(50)->withQueryString(),
            'filters' => $request->only(['canal', 'ip', 'bots', 'dias']),
            'days' => $this->days($request),
            'ranges' => self::RANGES,
            'channels' => SourceClassifier::LABELS,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        return CsvExport::download($this->filtered($request), [
            'Fecha' => 'created_at',
            'Página' => 'path',
            'Estado HTTP' => 'status',
            'Inicio de sesión' => fn ($v) => $v->is_entry ? 'Sí' : 'No',
            'Canal' => fn ($v) => $v->channel_label,
            'Fuente' => 'source_name',
            'Sitio de origen' => 'referrer_host',
            'utm_source' => 'utm_source',
            'utm_medium' => 'utm_medium',
            'utm_campaign' => 'utm_campaign',
            'IP' => 'ip',
            'País' => 'country',
            'Idioma' => 'language',
            'Dispositivo' => 'device',
            'Navegador' => 'browser',
            'Sistema' => 'os',
            'Rastreador' => 'bot_name',
            'User-Agent' => 'user_agent',
        ], 'visitas-'.now()->format('Y-m-d').'.csv');
    }

    private function days(Request $request): int
    {
        $days = (int) $request->query('dias', 30);

        return array_key_exists($days, self::RANGES) ? $days : 30;
    }

    private function filtered(Request $request): Builder
    {
        $r = MetricsReport::lastDays($this->days($request));

        return Visit::query()
            ->whereBetween('created_at', [$r->from, $r->to])
            ->when(! $request->boolean('bots'), fn ($q) => $q->where('is_bot', false))
            ->when($request->query('canal'), fn ($q, $c) => $q->where('channel', $c))
            ->when($request->query('ip'), fn ($q, $ip) => $q->where('ip', $ip));
    }
}

@extends('layouts.admin')

@section('title', 'Métricas')

@php
    $t = $r->totals();
    $daily = $r->daily();
    $maxDay = max(1, $daily->max('views'));
    $n = fn ($v) => number_format((int) $v, 0, ',', '.');
@endphp

@section('content')
    <div class="toolbar">
        <nav class="segmented" aria-label="Periodo">
            @foreach ($ranges as $d => $label)
                <a href="{{ route('admin.metrics.index', ['dias' => $d]) }}" @class(['is-active' => $d === $days])>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="search">
            <a href="{{ route('admin.metrics.visits', ['dias' => $days]) }}" class="btn btn-secondary">Ver visitas una a una</a>
            <a href="{{ route('admin.metrics.export', ['dias' => $days]) }}" class="btn btn-primary">Exportar CSV</a>
        </div>
    </div>

    <div class="cards">
        <div class="card"><span class="card-title">Páginas vistas</span><span class="card-value">{{ $n($t['pageviews']) }}</span></div>
        <div class="card"><span class="card-title">Sesiones</span><span class="card-value">{{ $n($t['sessions']) }}</span><span class="muted small">{{ $n($t['visitors']) }} visitantes únicos (por IP)</span></div>
        <div class="card card-gold"><span class="card-title">Llegaron por IA</span><span class="card-value">{{ $n($t['ai_entries']) }}</span><span class="muted small">ChatGPT, Claude, Gemini, Perplexity…</span></div>
        <div class="card"><span class="card-title">Llegaron por buscadores</span><span class="card-value">{{ $n($t['search_entries']) }}</span></div>
        <div class="card"><span class="card-title">Clics registrados</span><span class="card-value">{{ $n($t['clicks']) }}</span><span class="muted small">Tiempo medio en página: {{ $t['avg_seconds'] !== null ? gmdate($t['avg_seconds'] >= 3600 ? 'H:i:s' : 'i:s', $t['avg_seconds']) : '—' }}</span></div>
        <div class="card"><span class="card-title">Voluntarios / aportes</span><span class="card-value">{{ $n($t['volunteers']) }} / {{ $n($t['donations']) }}</span><span class="muted small">en el periodo</span></div>
    </div>

    <section class="panel">
        <h2 class="panel-title">Visitas por día</h2>
        <div class="daychart" role="img" aria-label="Páginas vistas por día">
            @foreach ($daily as $d)
                <div class="daychart-col" title="{{ $d['day']->translatedFormat('j M') }}: {{ $d['views'] }} páginas vistas, {{ $d['sessions'] }} sesiones, {{ $d['ai'] }} por IA">
                    <span class="daychart-bar" style="height: {{ $d['views'] ? max(3, round($d['views'] / $maxDay * 100)) : 0 }}%">
                        @if ($d['ai'])<span class="daychart-ai" style="height: {{ round($d['ai'] / max(1, $d['views']) * 100) }}%"></span>@endif
                    </span>
                </div>
            @endforeach
        </div>
        <div class="daychart-axis muted small">
            <span>{{ $daily->first()['day']->translatedFormat('j M') }}</span>
            <span><i class="key key-views"></i> páginas vistas <i class="key key-ai"></i> sesiones desde IA</span>
            <span>{{ $daily->last()['day']->translatedFormat('j M') }}</span>
        </div>
    </section>

    <div class="grid-panels">
        <section class="panel">
            <h2 class="panel-title">¿Por dónde llegan?</h2>
            @include('admin.metrics._bars', ['rows' => $r->channels()])
        </section>
        <section class="panel">
            <h2 class="panel-title">Asistentes de IA que recomiendan el sitio</h2>
            @include('admin.metrics._bars', ['rows' => $r->aiAssistants()])
            <h3 class="panel-sub">Páginas a las que llegan desde IA</h3>
            @include('admin.metrics._bars', ['rows' => $r->aiLandingPages()])
        </section>
        <section class="panel">
            <h2 class="panel-title">Fuentes específicas</h2>
            @include('admin.metrics._bars', ['rows' => $r->sources()->map(fn ($s) => (object) ['label' => (\App\Support\Analytics\SourceClassifier::LABELS[$s->channel] ?? $s->channel).($s->source_name ? ' · '.$s->source_name : ''), 'total' => (int) $s->total])])
        </section>
        <section class="panel">
            <h2 class="panel-title">Páginas más vistas</h2>
            @include('admin.metrics._bars', ['rows' => $r->topPages()])
        </section>
        <section class="panel">
            <h2 class="panel-title">Clics más frecuentes</h2>
            @include('admin.metrics._bars', ['rows' => $r->clicks()->map(fn ($c) => (object) ['label' => ($c->type !== 'click' ? '['.$c->type.'] ' : '').($c->label ?: 'sin texto'), 'total' => (int) $c->total])])
            <h3 class="panel-sub">Hasta dónde bajan en la página</h3>
            @include('admin.metrics._bars', ['rows' => $r->scroll()])
            <p class="muted small">Clics y desplazamiento solo se miden en visitantes que aceptan las cookies de medición.</p>
        </section>
        <section class="panel">
            <h2 class="panel-title">Dispositivos y navegadores</h2>
            @include('admin.metrics._bars', ['rows' => $r->breakdown('device')->map(fn ($d) => (object) ['label' => ['movil' => 'Móvil', 'escritorio' => 'Escritorio', 'tablet' => 'Tableta'][$d->key] ?? $d->key, 'total' => $d->total])])
            <h3 class="panel-sub">Navegador</h3>
            @include('admin.metrics._bars', ['rows' => $r->breakdown('browser'), 'empty' => 'Otro'])
            <h3 class="panel-sub">Sistema</h3>
            @include('admin.metrics._bars', ['rows' => $r->breakdown('os'), 'empty' => 'Otro'])
        </section>
        <section class="panel">
            <h2 class="panel-title">IPs con más actividad</h2>
            @php $ips = $r->topIps(); @endphp
            @if ($ips->isEmpty())
                <p class="muted small">Sin datos en este periodo.</p>
            @else
                <table class="table table-compact">
                    <thead><tr><th>IP</th><th class="num">Páginas</th><th class="num">Sesiones</th><th>Última</th></tr></thead>
                    <tbody>
                        @foreach ($ips as $ip)
                            <tr>
                                <td><a href="{{ route('admin.metrics.visits', ['dias' => $days, 'ip' => $ip->ip]) }}">{{ $ip->ip }}</a></td>
                                <td class="num">{{ $n($ip->total) }}</td>
                                <td class="num">{{ $n($ip->sessions) }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($ip->last_seen)->format('d/m H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <p class="muted small">Ubicación aproximada: copie la IP en un servicio de geolocalización. La IP es un dato personal: no la comparta fuera del equipo.</p>
        </section>
        <section class="panel">
            <h2 class="panel-title">Campañas (enlaces con utm)</h2>
            @include('admin.metrics._bars', ['rows' => $r->campaigns()->map(fn ($c) => (object) ['label' => collect([$c->utm_source, $c->utm_medium, $c->utm_campaign])->filter()->implode(' / '), 'total' => (int) $c->total])])
            <h3 class="panel-sub">Voluntarios según cómo llegaron</h3>
            @include('admin.metrics._bars', ['rows' => $r->volunteersBySource(), 'empty' => 'Sin dato'])
        </section>
        <section class="panel">
            <h2 class="panel-title">Rastreadores de IA y buscadores</h2>
            <p class="muted small">{{ $n($t['ai_bot_hits']) }} lecturas de rastreadores de IA de {{ $n($t['bot_hits']) }} en total. Que lean el sitio es la base para que lo recomienden.</p>
            @php $bots = $r->bots(); @endphp
            @if ($bots->isNotEmpty())
                <table class="table table-compact">
                    <thead><tr><th>Rastreador</th><th class="num">Lecturas</th><th>Última</th></tr></thead>
                    <tbody>
                        @foreach ($bots as $b)
                            <tr>
                                <td>{{ $b->bot_name }} @if ($b->is_ai_bot)<span class="pill pill-warn">IA</span>@endif</td>
                                <td class="num">{{ $n($b->total) }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($b->last_seen)->format('d/m H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <h3 class="panel-sub">Lo que más leen los rastreadores de IA</h3>
                @include('admin.metrics._bars', ['rows' => $r->aiBotPages()])
            @endif
        </section>
        @php $nf = $r->notFound(); @endphp
        @if ($nf->isNotEmpty())
            <section class="panel">
                <h2 class="panel-title">Páginas no encontradas (404)</h2>
                @include('admin.metrics._bars', ['rows' => $nf])
            </section>
        @endif
    </div>
@endsection

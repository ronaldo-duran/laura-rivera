@extends('layouts.admin')

@section('title', 'Visitas una a una')

@section('content')
    <div class="toolbar">
        <form method="GET" class="search">
            <select name="dias" aria-label="Periodo">
                @foreach ($ranges as $d => $label)
                    <option value="{{ $d }}" @selected($d === $days)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="canal" aria-label="Canal">
                <option value="">Todos los canales</option>
                @foreach ($channels as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['canal'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="search" name="ip" value="{{ $filters['ip'] ?? '' }}" placeholder="IP" aria-label="IP" style="min-width: 150px">
            <label class="check"><input type="checkbox" name="bots" value="1" @checked($filters['bots'] ?? false)> Incluir rastreadores</label>
            <button class="btn btn-secondary" type="submit">Filtrar</button>
        </form>
        <div class="search">
            <a href="{{ route('admin.metrics.index', ['dias' => $days]) }}" class="btn btn-secondary">← Resumen</a>
            <a href="{{ route('admin.metrics.export', request()->query()) }}" class="btn btn-primary">Exportar CSV</a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table table-compact">
            <thead>
                <tr><th>Fecha</th><th>Página</th><th>Origen</th><th>IP</th><th>Dispositivo</th><th>Navegador</th></tr>
            </thead>
            <tbody>
                @forelse ($visits as $v)
                    <tr>
                        <td>{{ $v->created_at->format('d/m H:i:s') }}@if ($v->is_entry)<br><span class="pill">Entrada</span>@endif</td>
                        <td>{{ $v->path }}@if ($v->status !== 200) <span class="pill pill-off">{{ $v->status }}</span>@endif</td>
                        <td>
                            {{ $v->channel_label }}@if ($v->source_name) · {{ $v->source_name }}@endif
                            @if ($v->bot_name)<br><small class="muted">{{ $v->bot_name }}</small>@endif
                            @if ($v->referrer_host && $v->channel !== 'interno')<br><small class="muted">{{ $v->referrer_host }}</small>@endif
                        </td>
                        <td><a href="{{ route('admin.metrics.visits', array_merge(request()->query(), ['ip' => $v->ip])) }}">{{ $v->ip }}</a>@if ($v->country) <small class="muted">{{ $v->country }}</small>@endif</td>
                        <td>{{ $v->device }}@if ($v->os) · {{ $v->os }}@endif</td>
                        <td>{{ $v->browser ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No hay visitas con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $visits->links() }}
@endsection

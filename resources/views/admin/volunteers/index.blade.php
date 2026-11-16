@extends('layouts.admin')

@section('title', 'Voluntarios')

@section('content')
    <div class="cards">
        <div class="card"><span class="card-title">Total registrados</span><span class="card-value">{{ number_format($total, 0, ',', '.') }}</span></div>
        <div class="card"><span class="card-title">Esta semana</span><span class="card-value">{{ number_format($thisWeek, 0, ',', '.') }}</span></div>
    </div>

    <div class="toolbar">
        <form method="GET" class="search">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre, documento, correo o celular" aria-label="Buscar">
            <select name="barrio" aria-label="Barrio">
                <option value="">Todos los barrios</option>
                @foreach (config('laura.barrios') as $b)
                    <option value="{{ $b }}" @selected(($filters['barrio'] ?? '') === $b)>{{ $b }}</option>
                @endforeach
            </select>
            <select name="apoyo" aria-label="Tipo de apoyo">
                <option value="">Todo tipo de apoyo</option>
                @foreach (config('laura.tipos_voluntariado') as $t)
                    <option value="{{ $t }}" @selected(($filters['apoyo'] ?? '') === $t)>{{ $t }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary" type="submit">Filtrar</button>
        </form>
        @can(\App\Support\Permissions::VOLUNTEERS_EXPORT)
            <a href="{{ route('admin.volunteers.export', request()->query()) }}" class="btn btn-primary">Exportar CSV</a>
        @endcan
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Fecha</th><th>Nombre</th><th>Documento</th><th>Celular</th><th>Correo</th><th>Barrio</th><th>Apoyo</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($volunteers as $v)
                    <tr>
                        <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $v->full_name }}@if ($v->preferred_name)<br><small class="muted">“{{ $v->preferred_name }}”</small>@endif</td>
                        <td>{{ $v->document_type }} {{ $v->document_number }}</td>
                        <td><a href="https://wa.me/{{ str_starts_with($v->phone, '+') ? ltrim($v->phone, '+') : '57'.$v->phone }}" target="_blank" rel="noopener">{{ $v->phone }}</a></td>
                        <td><a href="mailto:{{ $v->email }}">{{ $v->email }}</a></td>
                        <td>{{ $v->neighborhood ?? '—' }}</td>
                        <td>{{ $v->help_type ?? '—' }}</td>
                        <td class="actions"><a href="{{ route('admin.volunteers.show', $v) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">Aún no hay voluntarios con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $volunteers->links() }}
@endsection

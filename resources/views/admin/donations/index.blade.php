@extends('layouts.admin')

@section('title', 'Donaciones')

@php use App\Models\Donation; @endphp

@section('content')
    <div class="cards">
        <div class="card"><span class="card-title">Total aprobado (histórico)</span><span class="card-value">{{ Donation::money($allApprovedTotal) }}</span></div>
        <div class="card"><span class="card-title">Aprobado con estos filtros</span><span class="card-value">{{ Donation::money($approvedTotal) }}</span><span class="muted small">{{ $approvedCount }} aportes</span></div>
    </div>

    <div class="toolbar">
        <form method="GET" class="search">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre, documento, correo o referencia" aria-label="Buscar">
            <select name="estado" aria-label="Estado">
                <option value="">Todos los estados</option>
                @foreach (Donation::STATUSES as $code => $label)
                    <option value="{{ $code }}" @selected(($filters['estado'] ?? '') === $code)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}" aria-label="Desde">
            <input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}" aria-label="Hasta">
            <button class="btn btn-secondary" type="submit">Filtrar</button>
        </form>
        @can(\App\Support\Permissions::DONATIONS_EXPORT)
            <a href="{{ route('admin.donations.export', request()->query()) }}" class="btn btn-primary">Exportar CSV</a>
        @endcan
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Fecha</th><th>Aportante</th><th>Documento</th><th class="num">Monto</th><th>Estado</th><th>Medio</th><th>Referencia</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($donations as $d)
                    <tr>
                        <td>{{ $d->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $d->full_name }}<br><small class="muted">{{ $d->email }}</small></td>
                        <td>{{ $d->document_type }} {{ $d->document_number }}</td>
                        <td class="num">{{ $d->amount_formatted }}</td>
                        <td>
                            <span @class(['pill', 'pill-ok' => $d->status === 'APPROVED', 'pill-off' => in_array($d->status, ['DECLINED', 'VOIDED', 'ERROR']), 'pill-warn' => in_array($d->status, ['PENDING', 'CREATED'])])>{{ $d->status_label }}</span>
                        </td>
                        <td>{{ $d->payment_method_type ?? '—' }}</td>
                        <td><code>{{ $d->reference }}</code></td>
                        <td class="actions"><a href="{{ route('admin.donations.show', $d) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty">No hay donaciones con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $donations->links() }}
@endsection

@extends('layouts.admin')

@section('title', 'Donación '.$donation->reference)

@section('content')
    <a href="{{ route('admin.donations.index') }}">← Volver a donaciones</a>

    <div class="panel">
        <dl class="detail">
            <dt>Estado</dt><dd>{{ $donation->status_label }}</dd>
            <dt>Monto</dt><dd>{{ $donation->amount_formatted }} {{ $donation->currency }}</dd>
            <dt>Referencia</dt><dd><code>{{ $donation->reference }}</code></dd>
            <dt>Transacción Wompi</dt><dd>{{ $donation->wompi_transaction_id ?? '—' }}</dd>
            <dt>Medio de pago</dt><dd>{{ $donation->payment_method_type ?? '—' }}</dd>
            <dt>Aportante</dt><dd>{{ $donation->full_name }}</dd>
            <dt>Documento</dt><dd>{{ $donation->document_type }} {{ $donation->document_number }}</dd>
            <dt>Correo</dt><dd>{{ $donation->email }}</dd>
            <dt>Celular</dt><dd>{{ $donation->phone ?? '—' }}</dd>
            <dt>Iniciada</dt><dd>{{ $donation->created_at->format('d/m/Y H:i') }}</dd>
            <dt>Pagada</dt><dd>{{ $donation->paid_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            <dt>Último cambio de estado</dt><dd>{{ $donation->status_updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            <dt>Aceptó términos</dt><dd>{{ $donation->terms_accepted_at->format('d/m/Y H:i') }}</dd>
            <dt>Origen de la visita</dt><dd>{{ $donation->source ?? '—' }}</dd>
        </dl>
    </div>
@endsection

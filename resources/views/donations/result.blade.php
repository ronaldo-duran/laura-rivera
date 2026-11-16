@extends('layouts.app')

@section('title', 'Resultado del aporte')

@push('head')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <section class="page-hero container thanks">
        @if ($donation && $donation->status === 'APPROVED')
            <span class="eyebrow">Aporte aprobado</span>
            <h1 class="display-sm">¡Gracias por su aporte de {{ $donation->amount_formatted }}!</h1>
            <p class="lead lead-lg">Su referencia es <strong>{{ $donation->reference }}</strong>. Wompi le enviará el comprobante a {{ $donation->email }}.</p>
        @elseif ($donation && in_array($donation->status, ['DECLINED', 'VOIDED', 'ERROR'], true))
            <span class="eyebrow">Pago no completado</span>
            <h1 class="display-sm">El pago no se pudo completar</h1>
            <p class="lead lead-lg">No se realizó ningún cobro. Puede intentarlo de nuevo con otro medio de pago.</p>
        @else
            <span class="eyebrow">Procesando</span>
            <h1 class="display-sm">Estamos confirmando su pago</h1>
            <p class="lead lead-lg">Algunos medios (como PSE) tardan unos minutos. Wompi le enviará la confirmación a su correo.</p>
        @endif
        <div class="btn-row">
            <a href="{{ route('home') }}" class="btn btn-blue">Volver al inicio</a>
            @if (! $donation || $donation->status !== 'APPROVED')
                <a href="{{ route('donations.create') }}" class="btn btn-outline">Intentar de nuevo</a>
            @endif
        </div>
    </section>
    <div class="spacer"></div>
@endsection

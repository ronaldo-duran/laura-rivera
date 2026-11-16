@extends('layouts.app')

@section('title', 'Donar')
@section('description', 'Apoye con un aporte voluntario el trabajo de Laura Rivera por Villa del Rosario. Pago seguro con Wompi.')

@section('content')
    <section class="section container grid-2 align-start">
        <div class="stack">
            <span class="eyebrow">Aportes</span>
            <h1 class="display-sm">Apoye un trabajo transparente</h1>
            <p class="lead">Cada aporte se registra con nombre y documento del aportante y se reporta conforme a la normativa colombiana aplicable.</p>
            <ul class="check-list">
                <li>Pago seguro a través de Wompi</li>
                <li>PSE, Nequi, tarjetas y otros medios</li>
                <li>Recibe la confirmación en su correo</li>
            </ul>
        </div>
        <div>
            @if (! $enabled)
                <div class="alert">Las donaciones en línea estarán disponibles muy pronto.</div>
            @else
                @if ($sandbox)
                    <div class="alert">Modo de pruebas: los pagos no son reales.</div>
                @endif
                @include('donations._form')
            @endif
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-donation-form]').forEach(function (form) {
        var box = form.querySelector('[data-custom-amount]');
        form.querySelectorAll('input[name="amount"]').forEach(function (r) {
            r.addEventListener('change', function () {
                var other = r.value === 'otro' && r.checked;
                box.hidden = !other;
                if (other) box.querySelector('input').focus();
            });
        });
    });
</script>
@endpush

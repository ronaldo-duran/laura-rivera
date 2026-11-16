<section id="donar" class="section container grid-2 align-center">
    <div class="stack">
        <span class="eyebrow">Aportes</span>
        <h2 class="h2">Apoye un trabajo transparente</h2>
        <p class="lead">Cada aporte se registra con nombre y documento y se reporta conforme a la normativa colombiana aplicable.</p>
    </div>
    <div class="form-card-white stack">
        <div class="amount-grid">
            @foreach (config('laura.montos_donacion') as $a)
                <a class="amount-option amount-link" href="{{ route('donations.create', ['monto' => $a]) }}"><span>${{ number_format($a, 0, ',', '.') }}</span></a>
            @endforeach
        </div>
        <a href="{{ route('donations.create') }}" class="btn btn-blue btn-block">Hacer un aporte</a>
        <span class="muted small-text">Pago seguro con PSE, Nequi o tarjeta a través de Wompi.</span>
    </div>
</section>

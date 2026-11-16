@php
    $amounts = config('laura.montos_donacion');
    $requested = (string) request()->query('monto', '');
    $default = in_array((int) $requested, $amounts, true) ? $requested : (string) ($amounts[1] ?? $amounts[0]);
    $selected = old('amount', $default);
    $fmt = fn ($v) => '$'.number_format($v, 0, ',', '.');
@endphp
<form method="POST" action="{{ route('donations.store') }}" class="form-card-white stack" id="aporte" novalidate data-donation-form>
    @csrf
    @if ($errors->any())
        <div class="alert alert-error" role="alert">Revise los campos marcados.</div>
    @endif

    <fieldset class="amounts @error('amount_value') has-error @enderror">
        <legend class="field">Monto del aporte</legend>
        <div class="amount-grid">
            @foreach ($amounts as $a)
                <label class="amount-option">
                    <input type="radio" name="amount" value="{{ $a }}" @checked($selected === (string) $a)>
                    <span>{{ $fmt($a) }}</span>
                </label>
            @endforeach
            <label class="amount-option">
                <input type="radio" name="amount" value="otro" @checked($selected === 'otro') data-other-amount>
                <span>Otro</span>
            </label>
        </div>
        <label class="field custom-amount" data-custom-amount @if($selected !== 'otro') hidden @endif>
            Escriba el monto (COP)
            <input type="text" name="custom_amount" value="{{ old('custom_amount') }}" inputmode="numeric" placeholder="Ej. 80.000">
        </label>
        @error('amount_value')<span class="field-error">{{ $message }}</span>@enderror
    </fieldset>

    <div class="form-grid">
        <label class="field full @error('full_name') has-error @enderror">
            Nombre completo
            <input type="text" name="full_name" value="{{ old('full_name') }}" required maxlength="150" autocomplete="name">
            @error('full_name')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label class="field @error('document_type') has-error @enderror">
            Tipo de documento
            <select name="document_type" required>
                <option value="">Seleccione…</option>
                @foreach (config('laura.tipos_documento') as $code => $label)
                    <option value="{{ $code }}" @selected(old('document_type') === $code)>{{ $label }}</option>
                @endforeach
            </select>
            @error('document_type')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label class="field @error('document_number') has-error @enderror">
            Número de documento
            <input type="text" name="document_number" value="{{ old('document_number') }}" required maxlength="25" autocomplete="off">
            @error('document_number')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label class="field @error('email') has-error @enderror">
            Correo electrónico
            <input type="email" name="email" value="{{ old('email') }}" required maxlength="190" autocomplete="email">
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label class="field @error('phone') has-error @enderror">
            Celular <span class="hint">(opcional)</span>
            <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="20" autocomplete="tel">
            @error('phone')<span class="field-error">{{ $message }}</span>@enderror
        </label>
    </div>

    <div class="hp-field" aria-hidden="true"><label>No llenar <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    <div class="@error('accept_terms') has-error @enderror">
        <label class="check">
            <input type="checkbox" name="accept_terms" value="1" required @checked(old('accept_terms'))>
            <span>
                Declaro que el aporte proviene de recursos propios y lícitos, y acepto los
                <a href="{{ route('terms') }}" target="_blank" rel="noopener">términos y condiciones</a> y la
                <a href="{{ route('privacy') }}" target="_blank" rel="noopener">política de privacidad</a>.
            </span>
        </label>
        @error('accept_terms')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <button type="submit" class="btn btn-blue btn-block">Continuar al pago seguro</button>
    <span class="muted small-text">Será redirigido a Wompi (Bancolombia) para pagar con PSE, Nequi, tarjeta u otros medios. No guardamos datos de tarjetas.</span>
</form>

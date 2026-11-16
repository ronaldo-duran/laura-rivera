{{-- Formulario de registro de voluntarios (se usa en la página propia y en el inicio). --}}
@php
    $docTypes = config('laura.tipos_documento');
    $treatments = config('laura.tratamientos');
@endphp
<form method="POST" action="{{ route('volunteers.store') }}" class="form-grid" id="registro" novalidate>
    @csrf
    @if ($errors->any())
        <div class="alert alert-error full" role="alert">Revise los campos marcados.</div>
    @endif

    <label class="field @error('document_type') has-error @enderror">
        Tipo de documento
        <select name="document_type" required>
            <option value="">Seleccione…</option>
            @foreach ($docTypes as $code => $label)
                <option value="{{ $code }}" @selected(old('document_type') === $code)>{{ $label }}</option>
            @endforeach
        </select>
        @error('document_type')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('document_number') has-error @enderror">
        Número de documento
        <input type="text" name="document_number" value="{{ old('document_number') }}" required maxlength="25" inputmode="text" autocomplete="off">
        @error('document_number')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('first_names') has-error @enderror">
        Nombres
        <input type="text" name="first_names" value="{{ old('first_names') }}" required maxlength="100" autocomplete="given-name">
        @error('first_names')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('last_names') has-error @enderror">
        Apellidos
        <input type="text" name="last_names" value="{{ old('last_names') }}" required maxlength="100" autocomplete="family-name">
        @error('last_names')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('preferred_name') has-error @enderror">
        Nombre preferido <span class="hint">(opcional) ¿Cómo le gusta que le llamen?</span>
        <input type="text" name="preferred_name" value="{{ old('preferred_name') }}" maxlength="60" autocomplete="nickname">
        @error('preferred_name')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('treatment') has-error @enderror">
        ¿Cómo nos referimos a usted?
        <select name="treatment" required>
            <option value="">Seleccione…</option>
            @foreach ($treatments as $code => $label)
                <option value="{{ $code }}" @selected(old('treatment') === $code)>{{ $label }}</option>
            @endforeach
        </select>
        @error('treatment')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('email') has-error @enderror">
        Correo electrónico
        <input type="email" name="email" value="{{ old('email') }}" required maxlength="190" autocomplete="email">
        @error('email')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('phone') has-error @enderror">
        Celular
        <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20" autocomplete="tel" placeholder="300 123 4567">
        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('neighborhood') has-error @enderror">
        Barrio o vereda <span class="hint">(opcional)</span>
        <select name="neighborhood">
            <option value="">Seleccione…</option>
            @foreach (config('laura.barrios') as $b)
                <option value="{{ $b }}" @selected(old('neighborhood') === $b)>{{ $b }}</option>
            @endforeach
        </select>
        @error('neighborhood')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    <label class="field @error('help_type') has-error @enderror">
        ¿Cómo quiere ayudar? <span class="hint">(opcional)</span>
        <select name="help_type">
            <option value="">Seleccione…</option>
            @foreach (config('laura.tipos_voluntariado') as $t)
                <option value="{{ $t }}" @selected(old('help_type') === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('help_type')<span class="field-error">{{ $message }}</span>@enderror
    </label>

    {{-- Trampa anti-bots: las personas no ven este campo --}}
    <div class="hp-field" aria-hidden="true">
        <label>No llenar <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="full @error('accept_terms') has-error @enderror">
        <label class="check">
            <input type="checkbox" name="accept_terms" value="1" required @checked(old('accept_terms'))>
            <span>
                Acepto los <a href="{{ route('terms') }}" target="_blank" rel="noopener">términos y condiciones</a>
                y autorizo el tratamiento de mis datos según la
                <a href="{{ route('privacy') }}" target="_blank" rel="noopener">política de privacidad</a>.
            </span>
        </label>
        @error('accept_terms')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <button type="submit" class="btn btn-red btn-block full">Inscribirme como voluntario</button>
</form>

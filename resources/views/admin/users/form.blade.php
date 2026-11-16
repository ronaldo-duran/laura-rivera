@extends('layouts.admin')

@section('title', $user->exists ? 'Editar usuario' : 'Nuevo usuario')

@section('content')
    @php $userRoles = old('roles', $user->exists ? $user->getRoleNames()->all() : []); @endphp
    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="panel form-stack" novalidate>
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="grid-2">
            <label class="field @error('name') has-error @enderror">
                <span>Nombre completo</span>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="120">
                @error('name')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field @error('email') has-error @enderror">
                <span>Correo</span>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="190">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field @error('password') has-error @enderror">
                <span>Contraseña @if ($user->exists)<small class="muted">(dejar vacío para no cambiar)</small>@endif</span>
                <input type="password" name="password" autocomplete="new-password" @unless($user->exists) required @endunless>
                <small class="muted">Mínimo 10 caracteres, con letras y números.</small>
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field">
                <span>Confirmar contraseña</span>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </label>
        </div>

        <fieldset class="fieldset @error('roles') has-error @enderror">
            <legend>Roles</legend>
            <div class="check-grid">
                @foreach ($roles as $role)
                    <label class="check">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, $userRoles, true))>
                        {{ $role->name }}
                    </label>
                @endforeach
            </div>
            @error('roles')<span class="field-error">{{ $message }}</span>@enderror
        </fieldset>

        <label class="check">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" @checked(old('active', $user->active ?? true))> Cuenta activa
        </label>

        <div class="form-actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
@endsection

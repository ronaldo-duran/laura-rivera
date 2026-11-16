@extends('layouts.admin')

@section('title', $role->exists ? 'Editar rol' : 'Nuevo rol')

@section('content')
    @php
        $checked = old('permissions', $role->exists ? $role->permissions->pluck('name')->all() : [\App\Support\Permissions::DASHBOARD_VIEW]);
    @endphp
    <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="panel form-stack" novalidate>
        @csrf
        @if ($role->exists) @method('PUT') @endif

        <label class="field @error('name') has-error @enderror">
            <span>Nombre del rol</span>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required maxlength="60" placeholder="p. ej. coordinación">
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <fieldset class="fieldset">
            <legend>Permisos</legend>
            <p class="muted small">“Ver el panel de inicio” es necesario para poder ingresar al panel.</p>
            <div class="check-grid">
                @foreach ($permissions as $name => $label)
                    <label class="check">
                        <input type="checkbox" name="permissions[]" value="{{ $name }}" @checked(in_array($name, $checked, true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="form-actions">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
@endsection

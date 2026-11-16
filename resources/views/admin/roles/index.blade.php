@extends('layouts.admin')

@section('title', 'Roles y permisos')

@section('content')
    <div class="toolbar">
        <p class="muted">Cada rol define qué secciones del panel puede ver un usuario. El administrador siempre tiene acceso completo.</p>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">Nuevo rol</a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Rol</th><th>Permisos</th><th>Usuarios</th><th></th></tr></thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td><strong>{{ $role->name }}</strong></td>
                        <td>
                            @if ($role->name === \App\Support\Permissions::ROLE_ADMIN)
                                <span class="pill pill-ok">Todos</span>
                            @else
                                @forelse ($role->permissions as $perm)
                                    <span class="pill">{{ \App\Support\Permissions::label($perm->name) }}</span>
                                @empty
                                    <span class="muted">Sin permisos</span>
                                @endforelse
                            @endif
                        </td>
                        <td>{{ $role->users_count }}</td>
                        <td class="actions">
                            @unless ($role->name === \App\Support\Permissions::ROLE_ADMIN)
                                <a href="{{ route('admin.roles.edit', $role) }}">Editar</a>
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" data-confirm="¿Eliminar el rol {{ $role->name }}?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-link danger">Eliminar</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

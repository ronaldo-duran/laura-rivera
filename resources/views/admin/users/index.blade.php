@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content')
    <div class="toolbar">
        <form method="GET" class="search">
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre o correo" aria-label="Buscar">
            <button class="btn btn-secondary" type="submit">Buscar</button>
        </form>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Nuevo usuario</a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Nombre</th><th>Correo</th><th>Roles</th><th>Estado</th><th>Último ingreso</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>@foreach ($u->roles as $r)<span class="pill">{{ $r->name }}</span>@endforeach</td>
                        <td>@if ($u->active)<span class="pill pill-ok">Activo</span>@else<span class="pill pill-off">Inactivo</span>@endif</td>
                        <td>{{ $u->last_login_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="actions">
                            <a href="{{ route('admin.users.edit', $u) }}">Editar</a>
                            @unless ($u->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" data-confirm="¿Eliminar a {{ $u->email }}?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-link danger">Eliminar</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No hay usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
@endsection

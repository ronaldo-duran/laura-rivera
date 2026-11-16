@extends('layouts.admin')

@section('title', $volunteer->full_name)

@section('content')
    <a href="{{ route('admin.volunteers.index') }}">← Volver a voluntarios</a>

    <div class="panel">
        <dl class="detail">
            <dt>Saludo sugerido</dt><dd>{{ $volunteer->greeting }}</dd>
            <dt>Nombres</dt><dd>{{ $volunteer->first_names }}</dd>
            <dt>Apellidos</dt><dd>{{ $volunteer->last_names }}</dd>
            <dt>Nombre preferido</dt><dd>{{ $volunteer->preferred_name ?? '—' }}</dd>
            <dt>Trato</dt><dd>{{ $volunteer->treatment_label }}</dd>
            <dt>Documento</dt><dd>{{ $volunteer->document_type_label }} · {{ $volunteer->document_number }}</dd>
            <dt>Correo</dt><dd><a href="mailto:{{ $volunteer->email }}">{{ $volunteer->email }}</a></dd>
            <dt>Celular</dt><dd>{{ $volunteer->phone }}</dd>
            <dt>Barrio o vereda</dt><dd>{{ $volunteer->neighborhood ?? '—' }}</dd>
            <dt>Tipo de apoyo</dt><dd>{{ $volunteer->help_type ?? '—' }}</dd>
            <dt>Registrado</dt><dd>{{ $volunteer->created_at->format('d/m/Y H:i') }}</dd>
            <dt>Aceptó términos</dt><dd>{{ $volunteer->terms_accepted_at->format('d/m/Y H:i') }} (versión {{ $volunteer->terms_version }})</dd>
            <dt>Origen de la visita</dt><dd>{{ $volunteer->source ?? '—' }}</dd>
            <dt>IP</dt><dd>{{ $volunteer->ip_address ?? '—' }}</dd>
        </dl>
    </div>

    @can(\App\Support\Permissions::VOLUNTEERS_DELETE)
        <form method="POST" action="{{ route('admin.volunteers.destroy', $volunteer) }}"
              data-confirm="¿Eliminar definitivamente los datos de {{ $volunteer->full_name }}? Úselo cuando la persona pida la supresión de sus datos.">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Eliminar datos (supresión)</button>
        </form>
    @endcan
@endsection

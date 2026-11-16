@extends('layouts.admin')

@section('title', 'Noticias')

@section('content')
    <div class="toolbar">
        <form method="GET" class="search">
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por título o etiqueta" aria-label="Buscar">
            <button class="btn btn-secondary" type="submit">Buscar</button>
        </form>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">Nueva noticia</a>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Fecha</th><th>Título</th><th>Etiqueta</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($news as $n)
                    @php
                        $scheduled = $n->published && $n->published_at && $n->published_at->isFuture();
                    @endphp
                    <tr>
                        <td>{{ optional($n->published_at)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $n->title }}</td>
                        <td>{{ $n->tag ?? '—' }}</td>
                        <td>
                            @if (! $n->published)<span class="pill pill-off">Borrador</span>
                            @elseif ($scheduled)<span class="pill pill-warn">Programada</span>
                            @else<span class="pill pill-ok">Publicada</span>@endif
                        </td>
                        <td class="actions">
                            @if ($n->published && ! $scheduled)
                                <a href="{{ route('news.show', $n) }}" target="_blank" rel="noopener">Ver</a>
                            @endif
                            <a href="{{ route('admin.news.edit', $n) }}">Editar</a>
                            <form method="POST" action="{{ route('admin.news.destroy', $n) }}" data-confirm="¿Eliminar esta noticia? No se puede deshacer.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-link danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Aún no hay noticias.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $news->links() }}
@endsection

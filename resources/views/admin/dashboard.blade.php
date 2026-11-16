@extends('layouts.admin')

@section('title', 'Inicio')

@section('content')
    <p class="muted">Hola, {{ auth()->user()->name }}. Este es el resumen de las secciones a las que tiene acceso.</p>

    @if (empty($cards))
        <div class="empty">Su rol aún no tiene secciones asignadas. Pida a un administrador que le asigne permisos.</div>
    @else
        <div class="cards">
            @foreach ($cards as $card)
                <div class="card">
                    <span class="card-title">{{ $card['title'] }}</span>
                    <span class="card-value">{{ $card['value'] }}</span>
                    @if (! empty($card['url']))
                        <a href="{{ $card['url'] }}" class="card-link">{{ $card['hint'] }} →</a>
                    @else
                        <span class="muted small">{{ $card['hint'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endsection

@extends('layouts.app')

@php
    $p = config('laura.persona');
    $hero = config('laura.hero');
    $t = config('laura.trayectoria');
@endphp

@section('content')
    {{-- Portada --}}
    <section class="hero container">
        <div class="hero-text">
            <span class="eyebrow">{{ $hero['antetitulo'] }}</span>
            <h1 class="display">{!! $hero['titulo'] !!}</h1>
            <p class="lead lead-lg">{{ $hero['subtitulo'] }}</p>
            <div class="btn-row">
                <a href="{{ route('proposals') }}" class="btn btn-blue">Conozca las propuestas</a>
                @if (Route::has('volunteers.create'))
                    <a href="{{ route('volunteers.create') }}" class="btn btn-outline">Únase como voluntario</a>
                @else
                    <a href="{{ route('about') }}" class="btn btn-outline">Conozca a Laura</a>
                @endif
            </div>
        </div>
        <figure class="hero-figure">
            <img src="{{ asset('assets/laura-rivera.png') }}" alt="Retrato de {{ $p['nombre_completo'] }}, empresaria de {{ $p['municipio'] }}" width="800" height="1000" fetchpriority="high">
            <figcaption class="hero-badge">
                <span class="badge-kicker">{{ $p['nombre_completo'] }}</span>
                <span class="badge-title">{{ $p['rol'] }}</span>
            </figcaption>
        </figure>
    </section>

    {{-- Cifras --}}
    <section class="band-blue" aria-label="Cifras">
        <div class="container stats">
            @foreach (config('laura.estadisticas') as $s)
                <div class="stat"><span class="stat-value">{{ $s['valor'] }}</span><span class="stat-text">{{ $s['texto'] }}</span></div>
            @endforeach
        </div>
    </section>

    {{-- Propuestas --}}
    <section id="propuestas" class="section container stack-lg">
        <div class="section-head-simple">
            <span class="eyebrow">Ideas para el municipio</span>
            <h2 class="h2">Propuestas para {{ $p['municipio'] }}</h2>
        </div>
        @include('partials.proposals')
    </section>

    {{-- Trayectoria --}}
    <section id="trayectoria" class="band-cream">
        <div class="container section grid-2 align-center">
            <figure class="photo-frame">
                <img src="{{ asset('assets/laura-rivera.png') }}" alt="{{ $p['nombre_completo'] }}" loading="lazy" width="800" height="1000">
            </figure>
            <div class="stack">
                <span class="eyebrow">Trayectoria</span>
                <h2 class="h2-sm">{{ $t['titulo'] }}</h2>
                <p class="lead">{{ $t['parrafo_1'] }}</p>
                <div class="mini-stats">
                    @foreach ($t['logros'] as $l)
                        <div><span class="mini-value">{{ $l['valor'] }}</span><span class="mini-text">{{ $l['texto'] }}</span></div>
                    @endforeach
                </div>
                <p>{{ $t['parrafo_2'] }}</p>
                <blockquote class="quote">“{{ $t['cita'] }}”</blockquote>
                <a href="{{ route('about') }}" class="text-link">Conozca su trayectoria completa →</a>
            </div>
        </div>
    </section>

    {{-- Noticias --}}
    @if ($news->isNotEmpty())
        <section id="noticias" class="section container stack-lg">
            <div class="section-head">
                <h2 class="h2">Últimas noticias</h2>
                <a href="{{ route('news.index') }}" class="text-link">Ver todas</a>
            </div>
            <div class="news-grid">
                @foreach ($news as $n)
                    @include('partials.news-card', ['n' => $n])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Voluntariado --}}
    @include('volunteers._section')

    {{-- Aportes --}}
    @include('donations._cta')

    @stack('home-sections')
@endsection

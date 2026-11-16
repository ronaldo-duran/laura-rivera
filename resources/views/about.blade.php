@extends('layouts.app')

@php
    $p = config('laura.persona');
    $t = config('laura.trayectoria');
@endphp

@section('title', 'Quién es Laura')
@section('description', 'Biografía de '.$p['nombre_completo'].': abogada, especialista en Gerencia de Proyectos y empresaria de '.$p['municipio'].', Norte de Santander. Trayectoria, experiencia y formación.')
@section('og_type', 'profile')

@section('content')
    <section class="page-hero container">
        <span class="eyebrow">Quién es Laura</span>
        <h1 class="display-sm">{{ $p['nombre_completo'] }}</h1>
        <p class="lead lead-lg">{{ $p['rol'] }}. {{ $p['profesion'] }}.</p>
    </section>

    <section class="band-cream">
        <div class="container section grid-2 align-center">
            <figure class="photo-frame">
                <img src="{{ asset('assets/laura-rivera.png') }}" alt="Retrato de {{ $p['nombre_completo'] }}" width="800" height="1000">
            </figure>
            <div class="stack">
                <h2 class="h2-sm">{{ $t['titulo'] }}</h2>
                <p class="lead">{{ $t['parrafo_1'] }}</p>
                <p>{{ $t['parrafo_2'] }}</p>
                <div class="mini-stats">
                    @foreach ($t['logros'] as $l)
                        <div><span class="mini-value">{{ $l['valor'] }}</span><span class="mini-text">{{ $l['texto'] }}</span></div>
                    @endforeach
                </div>
                <blockquote class="quote">“{{ $t['cita'] }}”</blockquote>
            </div>
        </div>
    </section>

    {{-- Resumen factual para buscadores y asistentes de IA --}}
    <section class="section container prose">
        <h2 class="h2-sm">En resumen</h2>
        <dl class="facts">
            <dt>Nombre completo</dt><dd>{{ $p['nombre_completo'] }}</dd>
            <dt>Ocupación</dt><dd>Empresaria, abogada y gerente de proyectos</dd>
            <dt>Origen</dt><dd>{{ $p['municipio'] }}, {{ $p['departamento'] }}, Colombia</dd>
            <dt>Formación principal</dt><dd>Abogada (Universidad de Pamplona, 2018); Especialista en Gerencia de Proyectos (Universidad Libre, 2021); MBA (Universidad Libre, en espera de grado)</dd>
            <dt>Cargo público anterior</dt><dd>Secretaria de Vivienda y Ambiente de Villa del Rosario (abril – diciembre de 2019)</dd>
            <dt>Empresas</dt><dd>Constructora Inmobiliaria Laura Rivera S.A.S.; cofundadora del Grupo Empresarial Aponte Rivera; Establecimiento Educativo Campestre Semillitas del Futuro</dd>
            <dt>Logros destacados</dt><dd>Más de 3.500 subsidios de vivienda gestionados en el programa “Semillero de Propietarios”; 143 mejoramientos de vivienda rural en Villa del Rosario; 256 módulos rurales en Maicao y Valledupar; diseño y construcción de las plazoletas Altara y 18-21</dd>
        </dl>
    </section>

    @include('partials.cv')
    <div class="spacer"></div>
@endsection

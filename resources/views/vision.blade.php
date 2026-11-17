@extends('layouts.app')

@php $p = config('laura.persona'); @endphp

@section('title', 'Visión del municipio')
@section('description', 'La visión de Laura Rivera para '.$p['municipio'].': siete ejes de trabajo en vivienda, seguridad, empleo, movilidad, salud, educación y patrimonio.')

@section('content')
    <section class="page-hero container">
        <span class="eyebrow">Una visión para el municipio</span>
        <h1 class="display-sm">Visión del municipio</h1>
        <p class="lead lead-lg">Siete ejes de trabajo pensados desde la experiencia en vivienda, gestión de proyectos y el contacto directo con barrios y veredas de {{ $p['municipio'] }}.</p>
    </section>

    <section class="section container stack-lg">
        @include('partials.vision')
    </section>

    {{-- Listado completo (sin pestañas) para lectura y rastreo --}}
    <section class="band-cream">
        <div class="container section stack-lg">
            <h2 class="h2-sm">Todos los ejes de la visión</h2>
            <div class="proposal-grid">
                @foreach (config('laura.ejes') as $eje)
                    <article class="proposal-card">
                        <h3 class="h4">{{ $eje['nombre'] }}</h3>
                        <p class="muted">{{ $eje['intro'] }}</p>
                        <ul class="dash-list">
                            @foreach ($eje['items'] as $item)<li>{{ $item }}</li>@endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@extends('layouts.app')

@section('title', 'Página no encontrada')
@section('description', 'La página que busca no existe o cambió de dirección.')

@push('head')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <section class="page-hero container stack-sm">
        <span class="eyebrow">Error 404</span>
        <h1 class="display-sm">No encontramos esta página</h1>
        <p class="lead">Puede que la dirección haya cambiado. Estas secciones le pueden servir:</p>
        <p>
            <a href="{{ route('home') }}" class="btn btn-blue">Ir al inicio</a>
            <a href="{{ route('about') }}" class="text-link">Quién es Laura</a> ·
            <a href="{{ route('proposals') }}" class="text-link">Propuestas</a> ·
            <a href="{{ route('news.index') }}" class="text-link">Noticias</a>
        </p>
    </section>
@endsection

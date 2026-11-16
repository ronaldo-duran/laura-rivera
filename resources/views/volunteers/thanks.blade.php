@extends('layouts.app')

@section('title', 'Gracias por sumarse')

@push('head')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <section class="page-hero container thanks">
        <span class="eyebrow">Registro recibido</span>
        <h1 class="display-sm">¡Gracias por sumarse{{ $greeting ? ', '.$greeting : '' }}!</h1>
        <p class="lead lead-lg">Un coordinador le escribirá por WhatsApp o correo en menos de 48 horas.</p>
        <div class="btn-row">
            <a href="{{ route('proposals') }}" class="btn btn-blue">Conozca las propuestas</a>
            <a href="{{ route('home') }}" class="btn btn-outline">Volver al inicio</a>
        </div>
    </section>
    <div class="spacer"></div>
@endsection

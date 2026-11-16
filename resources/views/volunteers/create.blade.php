@extends('layouts.app')

@section('title', 'Voluntarios')
@section('description', 'Inscríbase como voluntario y trabaje con Laura Rivera por Villa del Rosario desde su barrio o vereda.')

@section('content')
    <section class="page-hero container">
        <span class="eyebrow">Voluntariado</span>
        <h1 class="display-sm">Súmese como voluntario</h1>
        <p class="lead lead-lg">Llene el formulario y un coordinador le contactará en menos de 48 horas.</p>
    </section>
    @include('volunteers._section')
@endsection

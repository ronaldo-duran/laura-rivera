@extends('layouts.app')

@section('title', 'Noticias')
@section('description', 'Últimas noticias, recorridos y eventos de Laura Rivera en Villa del Rosario, Norte de Santander.')

@section('content')
    <section class="page-hero container">
        <span class="eyebrow">Sala de prensa</span>
        <h1 class="display-sm">Noticias</h1>
    </section>

    <section class="section-tight container stack-lg">
        @if ($news->isEmpty())
            <p class="lead">Pronto publicaremos novedades.</p>
        @else
            <div class="news-grid">
                @foreach ($news as $n)
                    @include('partials.news-card', ['n' => $n])
                @endforeach
            </div>
            {{ $news->links('partials.pagination') }}
        @endif
    </section>
@endsection

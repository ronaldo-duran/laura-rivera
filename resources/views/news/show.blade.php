@extends('layouts.app')

@section('title', $item->title)
@section('description', $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->body), 155))
@section('og_type', 'article')
@if ($item->image_path)
    @section('og_image', asset('storage/'.$item->image_path))
@endif

@push('jsonld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $item->title,
    'description' => $item->summary,
    'datePublished' => optional($item->published_at)->toIso8601String(),
    'dateModified' => $item->updated_at?->toIso8601String(),
    'image' => $item->image_path ? asset('storage/'.$item->image_path) : asset('assets/laura-rivera.png'),
    'mainEntityOfPage' => route('news.show', $item),
    'inLanguage' => 'es-CO',
    'about' => ['@id' => url('/').'#laura-rivera'],
    'author' => ['@type' => 'Person', 'name' => config('laura.persona.nombre_completo'), '@id' => url('/').'#laura-rivera'],
    'publisher' => ['@type' => 'Person', 'name' => config('laura.persona.nombre_completo')],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
    <article class="article container">
        <a href="{{ route('news.index') }}" class="text-link back-link">← Todas las noticias</a>
        <header class="stack-sm">
            <span class="news-meta">
                @if ($item->tag){{ $item->tag }} · @endif
                <time datetime="{{ optional($item->published_at)->toDateString() }}">{{ optional($item->published_at)->translatedFormat('j \d\e F \d\e Y') }}</time>
            </span>
            <h1 class="display-sm">{{ $item->title }}</h1>
            @if ($item->summary)<p class="lead lead-lg">{{ $item->summary }}</p>@endif
        </header>

        @if ($item->image_path)
            <figure class="article-figure">
                <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->image_caption ?: $item->title }}" width="1200" height="750">
                @if ($item->image_caption)<figcaption>{{ $item->image_caption }}</figcaption>@endif
            </figure>
        @endif

        <div class="prose article-body">
            @foreach (preg_split("/\R{2,}/", trim((string) $item->body)) as $para)
                @if (trim($para) !== '')<p>{!! nl2br(e(trim($para))) !!}</p>@endif
            @endforeach
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="band-cream">
            <div class="container section stack-lg">
                <h2 class="h2-sm">Más noticias</h2>
                <div class="news-grid">
                    @foreach ($related as $n)
                        @include('partials.news-card', ['n' => $n])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

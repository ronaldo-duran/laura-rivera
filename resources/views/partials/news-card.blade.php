<article class="news-card">
    <a href="{{ route('news.show', $n) }}" class="news-media" tabindex="-1" aria-hidden="true">
        @if ($n->image_path)
            <img src="{{ asset('storage/'.$n->image_path) }}" alt="{{ $n->image_caption ?: $n->title }}" loading="lazy" width="640" height="400">
        @else
            <span class="placeholder">{{ $n->image_caption ?: 'Noticia' }}</span>
        @endif
    </a>
    <span class="news-meta">
        @if ($n->tag){{ $n->tag }} · @endif
        <time datetime="{{ optional($n->published_at)->toDateString() }}">{{ optional($n->published_at)->translatedFormat('j M Y') }}</time>
    </span>
    <h3 class="news-title"><a href="{{ route('news.show', $n) }}">{{ $n->title }}</a></h3>
    @if ($n->summary)<p class="news-sum">{{ $n->summary }}</p>@endif
</article>

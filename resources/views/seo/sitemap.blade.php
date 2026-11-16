{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($static as $u)
    <url>
        <loc>{{ $u['loc'] }}</loc>
        <changefreq>{{ $u['changefreq'] }}</changefreq>
        <priority>{{ $u['priority'] }}</priority>
    </url>
@endforeach
@foreach ($news as $n)
    <url>
        <loc>{{ route('news.show', $n) }}</loc>
        <lastmod>{{ $n->updated_at?->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
</urlset>

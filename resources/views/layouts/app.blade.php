@php
    $p = config('laura.persona');
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle ? $pageTitle.' · '.$p['nombre'] : $p['nombre'].' · Empresaria de '.$p['municipio'];
    $description = trim($__env->yieldContent('description')) ?: $p['descripcion_seo'];
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogImage = trim($__env->yieldContent('og_image')) ?: asset('assets/laura-rivera.png');
    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
@endphp
<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="author" content="{{ $p['nombre_completo'] }}">
    <meta name="theme-color" content="#0b2a5b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="lr-metrics" content="{{ route('metrics.event', absolute: false) }}">

    {{-- Open Graph / redes sociales --}}
    <meta property="og:locale" content="es_CO">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $p['nombre'] }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="alternate" type="text/plain" title="Resumen para asistentes de IA" href="{{ route('llms') }}">
    <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">
    {{-- Favicon: monograma LR (navy/dorado) en varios formatos --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Caslon+Text:ital,wght@0,400;0,700;1,400&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    {{-- Datos estructurados: persona + sitio web --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Person',
                '@id' => url('/').'#laura-rivera',
                'name' => $p['nombre_completo'],
                'alternateName' => $p['nombre'],
                'jobTitle' => 'Empresaria y Gerente de Proyectos',
                'description' => $p['descripcion_seo'],
                'image' => asset('assets/laura-rivera.png'),
                'url' => url('/'),
                'gender' => 'Female',
                'nationality' => ['@type' => 'Country', 'name' => 'Colombia'],
                'homeLocation' => [
                    '@type' => 'Place',
                    'name' => $p['municipio'].', '.$p['departamento'].', Colombia',
                ],
                'alumniOf' => [
                    ['@type' => 'CollegeOrUniversity', 'name' => 'Universidad de Pamplona'],
                    ['@type' => 'CollegeOrUniversity', 'name' => 'Universidad Libre'],
                    ['@type' => 'CollegeOrUniversity', 'name' => 'Pontificia Universidad Javeriana'],
                ],
                'hasOccupation' => [
                    ['@type' => 'Occupation', 'name' => 'Abogada'],
                    ['@type' => 'Occupation', 'name' => 'Gerente de Proyectos'],
                ],
                'knowsAbout' => ['Vivienda de interés social', 'Gerencia de proyectos', 'Subsidios de vivienda', 'Derecho', 'Gestión inmobiliaria'],
                'worksFor' => [
                    ['@type' => 'Organization', 'name' => 'Constructora Inmobiliaria Laura Rivera S.A.S.'],
                    ['@type' => 'Organization', 'name' => 'Establecimiento Educativo Campestre Semillitas del Futuro'],
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#website',
                'url' => url('/'),
                'name' => $p['nombre'],
                'inLanguage' => 'es-CO',
                'about' => ['@id' => url('/').'#laura-rivera'],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @stack('jsonld')
    @stack('head')
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <div class="flag-bar" aria-hidden="true"><span></span><span></span><span></span></div>

    @include('partials.header')

    <main id="contenido">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.cookie-banner')

    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>

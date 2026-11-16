@php
    $p = config('laura.persona');
    $nav = [
        ['label' => 'Quién es Laura', 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Propuestas', 'href' => route('proposals'), 'active' => request()->routeIs('proposals')],
        ['label' => 'Hoja de vida', 'href' => route('about').'#hoja-de-vida', 'active' => false],
        ['label' => 'Noticias', 'href' => route('news.index'), 'active' => request()->routeIs('news.*')],
    ];
    if (Route::has('volunteers.create')) {
        $nav[] = ['label' => 'Voluntarios', 'href' => route('volunteers.create'), 'active' => request()->routeIs('volunteers.*')];
    }
@endphp
<header class="site-header">
    <div class="container header-inner">
        <a href="{{ route('home') }}" class="brand" aria-label="{{ $p['nombre'] }} — inicio">
            <span class="brand-mark" aria-hidden="true">LR</span>
            <span class="brand-text">
                <span class="brand-name">{{ $p['nombre'] }}</span>
                <span class="brand-sub">{{ $p['municipio'] }}, {{ $p['departamento'] }}</span>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
            <span class="sr-only">Abrir menú</span>
            <span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span><span class="nav-toggle-bar"></span>
        </button>

        <nav id="site-nav" class="site-nav" aria-label="Principal" data-nav>
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}" @class(['is-active' => $item['active']]) @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
            @if (Route::has('donations.create'))
                <a href="{{ route('donations.create') }}" class="btn btn-red nav-cta">Donar</a>
            @endif
        </nav>
    </div>
</header>

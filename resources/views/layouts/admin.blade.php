@php
    use App\Support\Permissions as P;
    $user = auth()->user();
    // Menú: solo aparecen las secciones que existen y que el usuario puede ver.
    $menu = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Inicio', 'can' => P::DASHBOARD_VIEW],
        ['route' => 'admin.news.index', 'match' => 'admin.news.*', 'label' => 'Noticias', 'can' => P::NEWS_MANAGE],
        ['route' => 'admin.volunteers.index', 'match' => 'admin.volunteers.*', 'label' => 'Voluntarios', 'can' => P::VOLUNTEERS_VIEW],
        ['route' => 'admin.donations.index', 'match' => 'admin.donations.*', 'label' => 'Donaciones', 'can' => P::DONATIONS_VIEW],
        ['route' => 'admin.metrics.index', 'match' => 'admin.metrics.*', 'label' => 'Métricas', 'can' => P::METRICS_VIEW],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Usuarios', 'can' => P::USERS_MANAGE],
        ['route' => 'admin.roles.index', 'match' => 'admin.roles.*', 'label' => 'Roles y permisos', 'can' => P::ROLES_MANAGE],
    ];
@endphp
<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · Admin Laura Rivera</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @stack('head')
</head>
<body class="admin">
    <div class="admin-shell">
        <aside class="admin-side" data-admin-side>
            <a href="{{ route('admin.dashboard') }}" class="admin-brand"><span class="brand-mark">LR</span> Panel</a>
            <nav class="admin-nav" aria-label="Panel">
                @foreach ($menu as $item)
                    @if (Route::has($item['route']) && $user->can($item['can']))
                        <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['match'])])>{{ $item['label'] }}</a>
                    @endif
                @endforeach
            </nav>
            <div class="admin-side-foot">
                <a href="{{ route('home') }}" target="_blank" rel="noopener">Ver sitio ↗</a>
                <span class="admin-version">v{{ config('laura.version') }}</span>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-top">
                <button type="button" class="admin-menu-btn" data-admin-toggle aria-label="Menú">☰</button>
                <h1 class="admin-title">@yield('title', 'Panel')</h1>
                <div class="admin-user">
                    <span class="admin-user-name">{{ $user->name }}</span>
                    <span class="admin-user-role">{{ $user->getRoleNames()->implode(', ') ?: 'Sin rol' }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-link">Salir</button>
                    </form>
                </div>
            </header>

            <main class="admin-content">
                @if (session('status'))
                    <div class="flash flash-ok" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="flash flash-err" role="alert">
                        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.querySelector('[data-admin-toggle]')?.addEventListener('click', function () {
            document.querySelector('[data-admin-side]').classList.toggle('is-open');
        });
        document.querySelectorAll('form[data-confirm]').forEach(function (f) {
            f.addEventListener('submit', function (e) { if (!window.confirm(f.dataset.confirm)) e.preventDefault(); });
        });
    </script>
    @stack('scripts')
</body>
</html>

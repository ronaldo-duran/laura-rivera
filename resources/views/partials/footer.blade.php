@php $p = config('laura.persona'); @endphp
<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col">
            <span class="footer-name">{{ $p['nombre'] }}</span>
            <span>{{ $p['rol'] }}</span>
            <span>{{ $p['municipio'] }}, {{ $p['departamento'] }}</span>
        </div>
        <nav class="footer-col" aria-label="Pie de página">
            <a href="{{ route('about') }}">Quién es Laura</a>
            <a href="{{ route('proposals') }}">Propuestas</a>
            <a href="{{ route('news.index') }}">Noticias</a>
            @if (Route::has('volunteers.create'))
                <a href="{{ route('volunteers.create') }}">Voluntarios</a>
            @endif
        </nav>
        <nav class="footer-col" aria-label="Legal">
            <a href="{{ route('terms') }}">Términos y condiciones</a>
            <a href="{{ route('privacy') }}">Política de privacidad y cookies</a>
            <button type="button" class="link-button" data-cookie-reopen>Preferencias de cookies</button>
        </nav>
    </div>
    <div class="container footer-legal">
        <span>© {{ date('Y') }} {{ $p['nombre_completo'] }}. Todos los derechos reservados.</span>
    </div>
    <div class="flag-bar flag-bar-thin" aria-hidden="true"><span></span><span></span><span></span></div>
</footer>

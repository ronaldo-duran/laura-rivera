@extends('layouts.app')

@php $p = config('laura.persona'); @endphp

@section('title', 'Política de privacidad y cookies')
@section('description', 'Cómo tratamos los datos personales de voluntarios, aportantes y visitantes del sitio de Laura Rivera.')

@section('content')
    <section class="page-hero container">
        <span class="eyebrow">Legal</span>
        <h1 class="display-sm">Política de privacidad y cookies</h1>
        <p class="muted">Última actualización: {{ \Illuminate\Support\Carbon::parse('2026-11-16')->translatedFormat('j \d\e F \d\e Y') }}</p>
    </section>

    <section class="section-tight container prose legal">
        <h2>1. Responsable del tratamiento</h2>
        <p>{{ $p['nombre_completo'] }}, {{ $p['municipio'] }}, {{ $p['departamento'] }}, Colombia. Correo de contacto: <a href="mailto:{{ $p['email'] }}">{{ $p['email'] }}</a>.</p>

        <h2>2. Datos que recogemos</h2>
        <ul>
            <li><strong>Voluntarios:</strong> tipo y número de documento, nombres y apellidos, nombre preferido, forma de trato preferida, correo electrónico, celular, barrio o sector y tipo de apoyo.</li>
            <li><strong>Aportantes:</strong> nombre, documento, correo y el estado y monto de la transacción reportados por Wompi. No almacenamos datos de tarjetas.</li>
            <li><strong>Visitantes:</strong> páginas visitadas, sitio de origen de la visita (por ejemplo, un buscador o un asistente de inteligencia artificial como ChatGPT, Claude o Gemini), parámetros de campaña, dirección IP, tipo de navegador y dispositivo, idioma, y clics dentro del sitio.</li>
        </ul>

        <h2>3. Finalidades</h2>
        <ul>
            <li>Coordinar actividades de voluntariado y contactarle.</li>
            <li>Gestionar, registrar y reportar los aportes recibidos.</li>
            <li>Elaborar estadísticas de uso del sitio para mejorar sus contenidos.</li>
            <li>Prevenir fraude y mantener la seguridad del sitio.</li>
        </ul>

        <h2>4. Derechos del titular</h2>
        <p>Conforme a la Ley 1581 de 2012, usted puede conocer, actualizar, rectificar y suprimir sus datos, revocar la autorización otorgada y presentar quejas ante la Superintendencia de Industria y Comercio. Para ejercer sus derechos escriba a <a href="mailto:{{ $p['email'] }}">{{ $p['email'] }}</a>; responderemos en los plazos legales.</p>

        <h2>5. Datos sensibles y menores</h2>
        <p>No solicitamos datos sensibles. Los datos de menores de edad solo se tratan con autorización de su representante legal y respetando sus derechos prevalentes.</p>

        <h2>6. Seguridad y conservación</h2>
        <p>Aplicamos medidas técnicas y administrativas razonables (acceso restringido por roles, conexiones cifradas). Conservamos los datos mientras sean necesarios para las finalidades descritas o lo exija la ley.</p>

        <h2 id="cookies">7. Cookies y datos de navegación</h2>
        <p>Usamos:</p>
        <ul>
            <li><strong>Cookies esenciales:</strong> necesarias para el funcionamiento y la seguridad del sitio (sesión y protección de formularios). No pueden desactivarse.</li>
            <li><strong>Cookies de medición:</strong> un identificador anónimo de visitante para contar visitas y clics de forma agregada. Solo se activan si usted elige “Aceptar” en el aviso de cookies.</li>
        </ul>
        <p>Registramos de forma básica las páginas vistas, el sitio de origen y la dirección IP para estadísticas y seguridad. La IP se considera un dato personal; se usa únicamente para estadísticas agregadas y seguridad, y no se comparte con terceros. No usamos cookies de publicidad ni vendemos información.</p>
        <p>Puede cambiar su elección en cualquier momento desde el enlace “Preferencias de cookies” en el pie de página.</p>

        <h2>8. Cambios</h2>
        <p>Podemos actualizar esta política. La versión vigente será la publicada en esta página.</p>
    </section>
@endsection

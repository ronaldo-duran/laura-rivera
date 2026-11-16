@extends('layouts.app')

@php $p = config('laura.persona'); @endphp

@section('title', 'Términos y condiciones')
@section('description', 'Términos y condiciones de uso del sitio web de Laura Rivera, registro de voluntarios y aportes.')

@section('content')
    <section class="page-hero container">
        <span class="eyebrow">Legal</span>
        <h1 class="display-sm">Términos y condiciones</h1>
        <p class="muted">Última actualización: {{ \Illuminate\Support\Carbon::parse('2026-11-16')->translatedFormat('j \d\e F \d\e Y') }}</p>
    </section>

    <section class="section-tight container prose legal">
        <h2>1. Objeto</h2>
        <p>Estos términos regulan el uso del sitio web de {{ $p['nombre_completo'] }} (en adelante, “el sitio”), el registro de personas voluntarias y la realización de aportes económicos voluntarios. Al usar el sitio o enviar cualquier formulario, usted acepta estos términos.</p>

        <h2>2. Uso del sitio</h2>
        <p>El contenido del sitio tiene fines informativos. Usted se compromete a usarlo de forma lícita, a no suplantar a otras personas, a no enviar información falsa y a no intentar afectar su funcionamiento o seguridad.</p>

        <h2>3. Registro de voluntarios</h2>
        <ul>
            <li>El registro es libre, voluntario y gratuito. No genera relación laboral, contractual ni remuneración alguna.</li>
            <li>Usted declara que los datos suministrados (tipo y número de documento, nombres, correo y celular) son veraces y le pertenecen.</li>
            <li>Si es menor de edad (por ejemplo, con Tarjeta de Identidad), declara contar con la autorización de su padre, madre o representante legal.</li>
            <li>Podremos contactarle por correo, llamada o WhatsApp para coordinar actividades. Puede pedir no ser contactado en cualquier momento.</li>
            <li>Nos reservamos el derecho de no aceptar o retirar registros que incumplan estos términos.</li>
        </ul>

        <h2>4. Aportes económicos</h2>
        <ul>
            <li>Los aportes son voluntarios y se procesan a través de la pasarela de pagos Wompi. El sitio no almacena datos de tarjetas ni credenciales bancarias.</li>
            <li>Los aportes se registran y reportan conforme a la normativa colombiana vigente que resulte aplicable.</li>
            <li>No se aceptan aportes de fuentes prohibidas por la ley ni de personas que no puedan acreditar el origen lícito de los recursos.</li>
            <li>Las solicitudes de reversión se atenderán según las políticas de la pasarela de pagos y la ley.</li>
        </ul>

        <h2>5. Datos personales</h2>
        <p>El tratamiento de sus datos personales se rige por nuestra <a href="{{ route('privacy') }}">Política de privacidad y cookies</a>, en cumplimiento de la Ley 1581 de 2012 y el Decreto 1377 de 2013. Al aceptar estos términos, usted autoriza el tratamiento de sus datos para las finalidades allí descritas.</p>

        <h2>6. Propiedad intelectual</h2>
        <p>Textos, fotografías, logotipos y diseño del sitio pertenecen a sus titulares. No pueden reproducirse con fines comerciales sin autorización previa.</p>

        <h2>7. Responsabilidad</h2>
        <p>Procuramos que la información sea exacta y esté actualizada, pero no garantizamos la ausencia de errores ni la disponibilidad permanente del sitio. Los enlaces a sitios de terceros son responsabilidad de esos terceros.</p>

        <h2>8. Cambios</h2>
        <p>Podemos modificar estos términos. La versión vigente será siempre la publicada en esta página.</p>

        <h2>9. Contacto</h2>
        <p>Para preguntas sobre estos términos escriba a <a href="mailto:{{ $p['email'] }}">{{ $p['email'] }}</a>.</p>

        <h2>10. Ley aplicable</h2>
        <p>Estos términos se rigen por las leyes de la República de Colombia.</p>
    </section>
@endsection

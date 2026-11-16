@php $p = config('laura.persona'); @endphp
<section id="hoja-de-vida" class="section container">
    <div class="section-head section-head-rule">
        <div class="stack-sm">
            <span class="eyebrow">Hoja de vida</span>
            <h2 class="h2">{{ $p['nombre_completo'] }}</h2>
        </div>
        <span class="muted">{{ $p['profesion'] }}</span>
    </div>
    <div class="grid-2 gap-lg">
        <div>
            <h3 class="label-heading">Experiencia</h3>
            @foreach (config('laura.experiencia') as $c)
                <article class="cv-item">
                    <div class="cv-top">
                        <h4 class="cv-role">{{ $c['rol'] }}</h4>
                        <span class="cv-date">{{ $c['fecha'] }}</span>
                    </div>
                    <span class="cv-org">{{ $c['org'] }}</span>
                    <p class="cv-detail">{{ $c['detalle'] }}</p>
                </article>
            @endforeach
        </div>
        <div>
            <h3 class="label-heading">Formación</h3>
            @foreach (config('laura.formacion') as $e)
                <div class="edu-item">
                    <span class="edu-title">{{ $e['titulo'] }}</span>
                    <span class="cv-date">{{ $e['fecha'] }}</span>
                    <span class="edu-org">{{ $e['org'] }}</span>
                </div>
            @endforeach
            <div class="note-box"><strong>Habilidades:</strong> {{ implode(' · ', config('laura.habilidades')) }}</div>
        </div>
    </div>
</section>

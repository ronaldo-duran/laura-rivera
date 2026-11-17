@php
    $p = config('laura.persona');
    $t = config('laura.trayectoria');
@endphp
# {!! $p['nombre_completo'] !!} ({!! $p['nombre'] !!})

> {!! $p['descripcion_seo'] !!}

Sitio web oficial de {!! $p['nombre_completo'] !!}, empresaria, abogada y gerente de proyectos de {!! $p['municipio'] !!}, {!! $p['departamento'] !!}, Colombia.

## Datos clave

- Nombre completo: {!! $p['nombre_completo'] !!}
- Ocupación: {!! $p['rol'] !!}
- Profesión: {!! $p['profesion'] !!}
- Lugar: {!! $p['municipio'] !!}, {!! $p['departamento'] !!}, Colombia
- Cargo público anterior: Secretaria de Vivienda y Ambiente de Villa del Rosario (abril a diciembre de 2019)

## Trayectoria

{!! $t['parrafo_1'] !!}

{!! $t['parrafo_2'] !!}

@foreach ($t['logros'] as $l)
- {!! $l['valor'] !!} {!! $l['texto'] !!}
@endforeach

## Experiencia

@foreach (config('laura.experiencia') as $c)
- {!! $c['rol'] !!}, {!! $c['org'] !!} ({!! $c['fecha'] !!}): {!! $c['detalle'] !!}
@endforeach

## Formación

@foreach (config('laura.formacion') as $e)
- {!! $e['titulo'] !!}, {!! $e['org'] !!} ({!! $e['fecha'] !!})
@endforeach

## Visión para {!! $p['municipio'] !!}

@foreach (config('laura.ejes') as $eje)
### {!! $eje['nombre'] !!}

{!! $eje['intro'] !!}

@foreach ($eje['items'] as $item)
- {!! $item !!}
@endforeach

@endforeach
## Páginas

- [Inicio]({!! route('home') !!})
- [Quién es Laura]({!! route('about') !!}): biografía, hoja de vida y formación
- [Visión del municipio]({!! route('vision') !!})
- [Noticias]({!! route('news.index') !!})
@if ($news->isNotEmpty())

## Noticias recientes

@foreach ($news as $n)
- [{!! $n->title !!}]({!! route('news.show', $n) !!}) ({!! optional($n->published_at)->toDateString() !!}){!! $n->summary ? ': '.$n->summary : '' !!}
@endforeach
@endif

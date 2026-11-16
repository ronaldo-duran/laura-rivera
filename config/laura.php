<?php

/*
|--------------------------------------------------------------------------
| Contenido del sitio de Laura Rivera
|--------------------------------------------------------------------------
| Datos relativamente estáticos (biografía, hoja de vida, propuestas).
| Las noticias viven en base de datos y se gestionan desde el panel admin.
| Lenguaje neutral: se presenta a Laura como EMPRESARIA, no como "candidata".
*/

return [

    // IPs de proxies/CDN de confianza separadas por coma, o "*". Vacío por defecto.
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'persona' => [
        'nombre' => 'Laura Rivera',
        'nombre_completo' => 'Laura Milagros Rivera Rodríguez',
        'rol' => 'Empresaria · Gerente de Proyectos',
        'profesion' => 'Abogada · Especialista en Gerencia de Proyectos',
        'municipio' => 'Villa del Rosario',
        'departamento' => 'Norte de Santander',
        'movimiento' => 'Movimiento Defensores de la Patria',
        'email' => 'contacto@laurarivera.co',
        'telefono' => '',
        'descripcion_seo' => 'Laura Milagros Rivera Rodríguez, empresaria y abogada de Villa del Rosario, Norte de Santander. Gerente de proyectos con más de 3.500 subsidios de vivienda gestionados y trabajo social en la cuna de la patria.',
    ],

    'hero' => [
        'antetitulo' => 'Villa del Rosario · Norte de Santander',
        'titulo' => 'Villa del Rosario, cuna de la patria, merece un trabajo <em>a su altura</em>.',
        'subtitulo' => 'Aquí nació Santander y se firmó la Constitución de 1821. Desde el trabajo social y empresarial impulsamos orden, empleo y orgullo para nuestro municipio, con cuentas claras y presencia en cada barrio y vereda.',
    ],

    'estadisticas' => [
        ['valor' => '+3.500', 'texto' => 'familias apoyadas con subsidios de vivienda'],
        ['valor' => '62', 'texto' => 'barrios y veredas visitados'],
        ['valor' => '1.240', 'texto' => 'voluntarios inscritos'],
    ],

    'trayectoria' => [
        'titulo' => 'Una rosariense que sabe ejecutar',
        'parrafo_1' => 'Laura Milagros Rivera Rodríguez es abogada de la Universidad de Pamplona y especialista en Gerencia de Proyectos de la Universidad Libre, donde culmina su MBA. Fue Secretaria de Vivienda y Ambiente de Villa del Rosario en 2019 y es cofundadora del Grupo Empresarial Aponte Rivera.',
        'parrafo_2' => 'Dirigió el diseño y la construcción de las plazoletas Altara y 18-21, y gerencia el Establecimiento Educativo Campestre Semillitas del Futuro.',
        'cita' => 'La tierra de Santander tiene que volver a ser ejemplo de ley y de orden para Colombia.',
        'logros' => [
            ['valor' => '+3.500', 'texto' => 'subsidios gestionados en "Semillero de Propietarios"'],
            ['valor' => '143', 'texto' => 'mejoramientos de vivienda rural en Villa del Rosario'],
            ['valor' => '256', 'texto' => 'módulos rurales en Maicao y Valledupar'],
        ],
    ],

    'ejes' => [
        ['id' => 'viv', 'nombre' => 'Vivienda', 'intro' => 'Desde la experiencia de más de 3.500 subsidios gestionados y 143 mejoramientos rurales en el municipio: techo digno y títulos en regla para las familias rosarienses.', 'items' => [
            'Programa municipal de mejoramiento de vivienda urbana y rural.',
            'Acompañamiento gratuito para acceder a subsidios nacionales.',
            'Legalización y titulación de predios en asentamientos.',
            'Banco de lotes para vivienda de interés social.',
        ]],
        ['id' => 'seg', 'nombre' => 'Seguridad', 'intro' => 'Recuperar el control del territorio con presencia policial, tecnología y cero tolerancia con el contrabando y la extorsión en la zona de frontera.', 'items' => [
            'Red de 120 cámaras conectadas a un centro de monitoreo municipal.',
            'Frentes de seguridad en los 62 barrios y veredas.',
            'Alumbrado LED en corredores de La Parada y la Autopista Internacional.',
            'Comisaría de familia con atención 24 horas.',
        ]],
        ['id' => 'emp', 'nombre' => 'Empleo', 'intro' => 'Convertir la frontera en oportunidad: comercio formal, emprendimiento y primer empleo para los jóvenes rosarienses.', 'items' => [
            'Centro de emprendimiento junto al Puente Simón Bolívar.',
            'Programa "Primer Empleo Rosariense" para 2.000 jóvenes.',
            'Ventanilla única para formalizar negocios en 72 horas.',
            'Mercado campesino semanal en el Parque Grancolombiano.',
        ]],
        ['id' => 'mov', 'nombre' => 'Movilidad', 'intro' => 'Vías dignas y un tránsito ordenado entre Villa del Rosario y Cúcuta.', 'items' => [
            'Pavimentación de 40 km de vías urbanas y terciarias.',
            'Semaforización inteligente en la Autopista Internacional.',
            'Ciclorruta Villa del Rosario – Cúcuta.',
            'Reorganización del transporte informal.',
        ]],
        ['id' => 'sal', 'nombre' => 'Salud', 'intro' => 'Atención cercana, sin filas eternas, en cada sector del municipio.', 'items' => [
            'Dos nuevos puestos de salud rurales.',
            'Agendamiento de citas por WhatsApp.',
            'Brigadas mensuales de salud en veredas.',
            'Atención priorizada a adultos mayores.',
        ]],
        ['id' => 'edu', 'nombre' => 'Educación', 'intro' => 'Formar ciudadanos que conozcan y honren la historia que nació en su tierra.', 'items' => [
            'Cátedra Santanderista en todos los colegios públicos.',
            'Conectividad gratuita en 30 sedes educativas.',
            'Transporte escolar rural.',
            'Becas de educación superior con universidades de la región.',
        ]],
        ['id' => 'pat', 'nombre' => 'Patrimonio', 'intro' => 'El Templo Histórico y la Casa Natal de Santander como motor de turismo y orgullo.', 'items' => [
            'Restauración del Parque Grancolombiano.',
            'Ruta turística "Cuna de la Gran Colombia".',
            'Festival anual de la Constitución de 1821.',
            'Guías turísticos formados entre jóvenes locales.',
        ]],
    ],

    'experiencia' => [
        ['rol' => 'Representante Legal y Gerente de Proyectos', 'org' => 'Consorcio Ingeniería Rural Rivera Fundhabitas Villa del Rosario', 'fecha' => 'Sep 2024 – actualidad', 'detalle' => 'Dirección de 143 mejoramientos de vivienda rural. Articulación con entidades públicas, fiduciarias y contratistas.'],
        ['rol' => 'Representante Legal y Gerente de Proyectos', 'org' => 'Consorcio Ingeniería Rural Rivera Fundhabitas Maicao y Valledupar', 'fecha' => 'Oct 2023 – actualidad', 'detalle' => 'Ejecución de 121 módulos rurales en Maicao y 135 en Valledupar.'],
        ['rol' => 'Gerente General y Coordinadora Académica', 'org' => 'Establecimiento Educativo Campestre Semillitas del Futuro', 'fecha' => 'Sep 2023 – actualidad', 'detalle' => 'Dirección administrativa, financiera y académica del colegio.'],
        ['rol' => 'Gerente General', 'org' => 'Asociación de Vivienda Popular Mi Sueño Mi Hogar', 'fecha' => 'Sep 2019 – actualidad', 'detalle' => 'Estrategia comercial y venta de lotes del proyecto "La Nueva Parada".'],
        ['rol' => 'Secretaria de Vivienda y Ambiente', 'org' => 'Alcaldía Municipal de Villa del Rosario', 'fecha' => 'Abr – Dic 2019', 'detalle' => 'Programas de vivienda y medio ambiente y atención a las necesidades habitacionales del municipio.'],
        ['rol' => 'Subgerente y Gerente de Proyectos', 'org' => 'Constructora Inmobiliaria Laura Rivera S.A.S.', 'fecha' => 'Jul 2016 – actualidad', 'detalle' => 'Más de 3.500 subsidios del programa "Semillero de Propietarios". Diseño y construcción de las plazoletas Altara y 18-21.'],
    ],

    'formacion' => [
        ['titulo' => 'MBA, Maestría en Administración de Negocios', 'org' => 'Universidad Libre, Cúcuta', 'fecha' => 'En espera de grado'],
        ['titulo' => 'Especialista en Gerencia de Proyectos', 'org' => 'Universidad Libre, Cúcuta', 'fecha' => '2021'],
        ['titulo' => 'Abogada', 'org' => 'Universidad de Pamplona, Villa del Rosario', 'fecha' => '2018'],
        ['titulo' => 'Diplomado en Gestión Inmobiliaria', 'org' => 'Pontificia Universidad Javeriana, Bogotá', 'fecha' => '2020'],
        ['titulo' => 'Técnico Laboral en Avalúos (Perito Avaluador)', 'org' => 'Centro de Estudios Derechos y Propiedad, Bogotá', 'fecha' => '2021'],
        ['titulo' => 'Diplomado en Docencia y Didáctica', 'org' => 'Universidad Francisco de Paula Santander', 'fecha' => '2023'],
        ['titulo' => 'Técnico Laboral en Atención a la Primera Infancia', 'org' => 'Centro de Formación Técnica de Colombia, Santa Marta', 'fecha' => '2023'],
        ['titulo' => 'Diplomado en Mecanismos Alternativos de Resolución de Conflictos', 'org' => 'Universidad de Pamplona', 'fecha' => '2017'],
        ['titulo' => 'Técnico en Administración e Informática', 'org' => 'Comfanorte, Cúcuta', 'fecha' => '2008'],
    ],

    'habilidades' => ['Liderazgo', 'Resolución de problemas', 'Pensamiento analítico', 'Gerencia de proyectos'],

    // Registro de voluntarios
    'tipos_documento' => [
        'CC' => 'Cédula de ciudadanía',
        'TI' => 'Tarjeta de identidad',
        'CE' => 'Cédula de extranjería',
        'PA' => 'Pasaporte',
        'PPT' => 'Permiso por Protección Temporal (PPT)',
        'PEP' => 'Permiso Especial de Permanencia (PEP)',
    ],

    // "¿Cómo nos referimos a usted?"
    'tratamientos' => [
        'senora' => 'Señora',
        'senor' => 'Señor',
        'nombre' => 'Solo por mi nombre',
    ],

    'terminos_version' => '2026-11-16',

    'barrios' => ['Centro histórico', 'La Parada', 'Juan Frío', 'Lomitas', 'San Gregorio', 'Palogordo', 'Otro'],

    'tipos_voluntariado' => ['Testigo electoral', 'Puerta a puerta', 'Logística y eventos', 'Redes sociales', 'Otro'],

    'montos_donacion' => [20000, 50000, 100000, 250000],
];

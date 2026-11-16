<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Laura Rivera recorrió La Parada y escuchó a 300 comerciantes',
                'tag' => 'Recorrido',
                'summary' => 'Presentó la propuesta de ventanilla única para la formalización de negocios en la frontera.',
                'body' => "Durante la jornada en La Parada, Laura Rivera acompañó a más de 300 comerciantes del corredor fronterizo y escuchó sus principales necesidades.\n\nAllí expuso la propuesta de una ventanilla única que permita formalizar un negocio en 72 horas, reduciendo los trámites y acercando las oportunidades a las familias rosarienses.",
                'published_at' => '2026-10-24 09:00:00',
            ],
            [
                'title' => 'Presentamos el plan de seguridad "Rosario Seguro"',
                'tag' => 'Propuesta',
                'summary' => '120 cámaras conectadas a un centro de monitoreo y frentes de seguridad en todos los barrios.',
                'body' => "El plan \"Rosario Seguro\" contempla una red de 120 cámaras conectadas a un centro de monitoreo municipal y frentes de seguridad en los 62 barrios y veredas del municipio.\n\nLa iniciativa busca recuperar el control del territorio con tecnología, presencia y trabajo articulado con la comunidad.",
                'published_at' => '2026-10-19 10:30:00',
            ],
            [
                'title' => 'Gran encuentro ciudadano en el Templo Histórico',
                'tag' => 'Evento',
                'summary' => 'Más de 2.000 rosarienses acompañaron la jornada de trabajo comunitario en la cuna de la patria.',
                'body' => "Más de 2.000 rosarienses se dieron cita en el Templo Histórico para una jornada de trabajo comunitario centrada en vivienda, empleo y patrimonio.\n\nEl encuentro reafirmó el compromiso con un municipio ordenado, con cuentas claras y orgulloso de su historia.",
                'published_at' => '2026-10-12 17:00:00',
            ],
        ];

        foreach ($items as $item) {
            News::firstOrCreate(
                ['slug' => News::uniqueSlug($item['title'])],
                $item + ['published' => true]
            );
        }
    }
}

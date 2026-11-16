<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos iniciales del sitio. Seguro de correr varias veces.
     * El primer administrador se crea con: php artisan app:crear-admin
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            NewsSeeder::class,
        ]);
    }
}

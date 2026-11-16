<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos iniciales del sitio.
     */
    public function run(): void
    {
        $this->call([
            NewsSeeder::class,
        ]);
    }
}

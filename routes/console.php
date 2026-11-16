<?php

use Illuminate\Support\Facades\Schedule;

// Requiere en Hostinger un cron cada minuto: php artisan schedule:run
// (opcional: sin cron, simplemente no se borran las métricas antiguas).
Schedule::command('app:podar-metricas')->dailyAt('03:30');

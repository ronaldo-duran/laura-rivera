<?php

namespace App\Console\Commands;

use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Console\Command;

class PruneMetrics extends Command
{
    protected $signature = 'app:podar-metricas {--dias= : Días a conservar (por defecto METRICS_RETENTION_DAYS)}';

    protected $description = 'Borra visitas y eventos de métricas más antiguos que el periodo de retención';

    public function handle(): int
    {
        $option = $this->option('dias');
        $days = (int) ($option !== null ? $option : config('laura.metricas.retencion_dias', 400));
        if ($days < 1) {
            $this->error('El número de días debe ser mayor que cero.');

            return self::FAILURE;
        }
        $cutoff = now()->subDays($days);

        $visits = Visit::where('created_at', '<', $cutoff)->delete();
        $events = VisitEvent::where('created_at', '<', $cutoff)->delete();

        $this->info("Se borraron {$visits} visitas y {$events} eventos anteriores al {$cutoff->format('Y-m-d')}.");

        return self::SUCCESS;
    }
}

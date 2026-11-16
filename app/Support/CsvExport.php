<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exporta una consulta a CSV listo para abrir en Excel (es-CO):
 * UTF-8 con BOM, separador punto y coma, y celdas neutralizadas contra
 * inyección de fórmulas (=, +, -, @).
 */
class CsvExport
{
    /**
     * @param  array<string, callable|string>  $columns  encabezado => atributo o función($fila)
     */
    public static function download(Builder $query, array $columns, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_keys($columns), ';', '"', '');

            $query->lazyById(500)->each(function ($row) use ($out, $columns) {
                $line = [];
                foreach ($columns as $col) {
                    $value = is_callable($col) ? $col($row) : data_get($row, $col);
                    $line[] = static::safe($value);
                }
                fputcsv($out, $line, ';', '"', '');
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function safe(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}

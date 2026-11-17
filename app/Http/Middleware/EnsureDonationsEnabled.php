<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege las rutas públicas de donación con el interruptor general
 * (config laura.donaciones.activas / env DONATIONS_ENABLED). Si está apagado,
 * /donar responde 404 antes de validar el formulario. No afecta al webhook.
 */
class EnsureDonationsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('laura.donaciones.activas'), 404);

        return $next($request);
    }
}

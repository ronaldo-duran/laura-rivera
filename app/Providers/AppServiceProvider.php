<?php

namespace App\Providers;

use App\Support\Permissions;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // El administrador puede todo, incluso permisos que se agreguen en el futuro.
        Gate::before(fn ($user) => $user->hasRole(Permissions::ROLE_ADMIN) ? true : null);

        // Proxies de confianza (p. ej. CDN de Hostinger o Cloudflare) para leer la IP real.
        // Vacío = se usa la IP de la conexión directa (no se puede falsificar por cabecera).
        if ($proxies = config('laura.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        Paginator::defaultView('partials.pagination');

        Route::resourceVerbs(['create' => 'nuevo', 'edit' => 'editar']);
    }
}

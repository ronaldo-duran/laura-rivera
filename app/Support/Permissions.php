<?php

namespace App\Support;

/**
 * Catálogo central de permisos y roles por defecto del panel.
 *
 * Para agregar un permiso nuevo: añádalo a ALL con su etiqueta y ejecute
 * `php artisan db:seed --class=RolesAndPermissionsSeeder` (es idempotente).
 * El rol administrador siempre tiene todos los permisos.
 */
final class Permissions
{
    public const DASHBOARD_VIEW = 'dashboard.ver';

    public const NEWS_MANAGE = 'noticias.gestionar';

    public const VOLUNTEERS_VIEW = 'voluntarios.ver';

    public const VOLUNTEERS_EXPORT = 'voluntarios.exportar';

    public const DONATIONS_VIEW = 'donaciones.ver';

    public const DONATIONS_EXPORT = 'donaciones.exportar';

    public const METRICS_VIEW = 'metricas.ver';

    public const USERS_MANAGE = 'usuarios.gestionar';

    public const ROLES_MANAGE = 'roles.gestionar';

    public const ROLE_ADMIN = 'administrador';

    public const ROLE_ACCOUNTING = 'contabilidad';

    public const ROLE_MARKETING = 'marketing';

    /** @return array<string, string> permiso => etiqueta */
    public static function all(): array
    {
        return [
            self::DASHBOARD_VIEW => 'Ver el panel de inicio',
            self::NEWS_MANAGE => 'Crear, editar y publicar noticias',
            self::VOLUNTEERS_VIEW => 'Ver voluntarios registrados',
            self::VOLUNTEERS_EXPORT => 'Exportar voluntarios (CSV)',
            self::DONATIONS_VIEW => 'Ver donaciones',
            self::DONATIONS_EXPORT => 'Exportar donaciones (CSV)',
            self::METRICS_VIEW => 'Ver métricas de visitas',
            self::USERS_MANAGE => 'Gestionar usuarios',
            self::ROLES_MANAGE => 'Gestionar roles y permisos',
        ];
    }

    /** @return array<string, array{label: string, permissions: list<string>}> */
    public static function defaultRoles(): array
    {
        return [
            self::ROLE_ADMIN => [
                'label' => 'Administrador',
                'permissions' => array_keys(self::all()),
            ],
            self::ROLE_ACCOUNTING => [
                'label' => 'Contabilidad',
                'permissions' => [self::DASHBOARD_VIEW, self::DONATIONS_VIEW, self::DONATIONS_EXPORT],
            ],
            self::ROLE_MARKETING => [
                'label' => 'Marketing',
                'permissions' => [self::DASHBOARD_VIEW, self::NEWS_MANAGE, self::METRICS_VIEW],
            ],
        ];
    }

    public static function label(string $permission): string
    {
        return self::all()[$permission] ?? $permission;
    }
}

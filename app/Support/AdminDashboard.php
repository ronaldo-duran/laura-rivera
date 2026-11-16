<?php

namespace App\Support;

use App\Models\News;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Support\Facades\Route;

/**
 * Tarjetas del inicio del panel. Cada módulo agrega la suya; solo se muestran
 * las que el usuario puede ver según sus permisos.
 */
class AdminDashboard
{
    /** @return list<array{title: string, value: string, hint: string, url: ?string}> */
    public static function cardsFor(User $user): array
    {
        $cards = [];

        if ($user->can(Permissions::NEWS_MANAGE)) {
            $cards[] = [
                'title' => 'Noticias publicadas',
                'value' => number_format(News::published()->count(), 0, ',', '.'),
                'hint' => 'Gestionar noticias',
                'url' => Route::has('admin.news.index') ? route('admin.news.index') : null,
            ];
        }

        if ($user->can(Permissions::VOLUNTEERS_VIEW)) {
            $cards[] = [
                'title' => 'Voluntarios registrados',
                'value' => number_format(Volunteer::count(), 0, ',', '.'),
                'hint' => 'Ver voluntarios',
                'url' => route('admin.volunteers.index'),
            ];
        }

        if ($user->can(Permissions::USERS_MANAGE)) {
            $cards[] = [
                'title' => 'Usuarios del panel',
                'value' => number_format(User::where('active', true)->count(), 0, ',', '.'),
                'hint' => 'Gestionar usuarios',
                'url' => route('admin.users.index'),
            ];
        }

        return $cards;
    }
}

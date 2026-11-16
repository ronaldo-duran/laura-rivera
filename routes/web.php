<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\Admin\VolunteerController as AdminVolunteerController;
use App\Support\Permissions;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sitio público
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/quien-es-laura', [PublicController::class, 'about'])->name('about');
Route::get('/propuestas', [PublicController::class, 'proposals'])->name('proposals');
Route::get('/noticias', [PublicController::class, 'newsIndex'])->name('news.index');
Route::get('/noticias/{news}', [PublicController::class, 'newsShow'])->name('news.show');
Route::get('/voluntarios', [VolunteerController::class, 'create'])->name('volunteers.create');
Route::post('/voluntarios', [VolunteerController::class, 'store'])->middleware('throttle:10,1')->name('volunteers.store');
Route::get('/voluntarios/gracias', [VolunteerController::class, 'thanks'])->name('volunteers.thanks');
Route::get('/terminos-y-condiciones', [PublicController::class, 'terms'])->name('terms');
Route::get('/politica-de-privacidad', [PublicController::class, 'privacy'])->name('privacy');

/*
|--------------------------------------------------------------------------
| SEO y rastreo (Google + asistentes de IA)
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

/*
|--------------------------------------------------------------------------
| Acceso al panel
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:20,1')->name('login.attempt');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Panel de administración (cada sección protegida por permiso)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'permission:'.Permissions::DASHBOARD_VIEW])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('permission:'.Permissions::VOLUNTEERS_VIEW)->group(function () {
        Route::get('/voluntarios', [AdminVolunteerController::class, 'index'])->name('volunteers.index');
        Route::get('/voluntarios/exportar', [AdminVolunteerController::class, 'export'])
            ->middleware('permission:'.Permissions::VOLUNTEERS_EXPORT)->name('volunteers.export');
        Route::get('/voluntarios/{volunteer}', [AdminVolunteerController::class, 'show'])->name('volunteers.show');
        Route::delete('/voluntarios/{volunteer}', [AdminVolunteerController::class, 'destroy'])
            ->middleware('permission:'.Permissions::VOLUNTEERS_DELETE)->name('volunteers.destroy');
    });

    Route::middleware('permission:'.Permissions::USERS_MANAGE)->group(function () {
        Route::resource('usuarios', UserController::class)
            ->parameters(['usuarios' => 'user'])->except('show')->names('users');
    });

    Route::middleware('permission:'.Permissions::ROLES_MANAGE)->group(function () {
        Route::resource('roles', RoleController::class)->except('show')->names('roles');
    });
});

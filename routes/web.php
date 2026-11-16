<?php

use App\Http\Controllers\PublicController;
use App\Http\Controllers\SeoController;
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

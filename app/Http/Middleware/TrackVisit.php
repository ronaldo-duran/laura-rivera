<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use App\Support\Analytics\SourceClassifier;
use App\Support\Analytics\UserAgent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registra cada página vista del sitio público: origen (IA, buscador, red social,
 * campaña…), página, dispositivo, IP y rastreadores. Nunca interrumpe la
 * respuesta: si algo falla, solo se anota en el log.
 */
class TrackVisit
{
    /** Rutas que no se miden (panel, acceso, webhooks y el propio colector). */
    private const SKIP = ['admin', 'admin/*', 'login', 'logout', 'webhooks/*', 'metricas/*', 'up', 'storage/*'];

    public const SESSION_SOURCE = 'lr_source';

    private const SESSION_ENTRY = 'lr_entry';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            try {
                $this->record($request, $response);
            } catch (\Throwable $e) {
                Log::warning('No se pudo registrar la visita: '.$e->getMessage());
            }
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! config('laura.metricas.activas', true) || ! $request->isMethod('GET') || $request->is(...self::SKIP)) {
            return false;
        }
        if ($request->user() !== null) {
            return false; // el equipo navegando el sitio no infla las cifras
        }
        $purpose = strtolower((string) ($request->header('Sec-Purpose') ?? $request->header('Purpose')));
        if (str_contains($purpose, 'prefetch') || str_contains($purpose, 'prerender')) {
            return false;
        }

        return in_array($response->getStatusCode(), [200, 404], true);
    }

    private function record(Request $request, Response $response): void
    {
        $ua = UserAgent::parse($request->userAgent());
        $refUrl = (string) $request->headers->get('referer', '');
        $refHost = $refUrl !== '' ? parse_url($refUrl, PHP_URL_HOST) : null;
        $utm = fn (string $k) => ($v = $request->query($k)) && is_string($v) ? mb_substr(trim($v), 0, 100) : null;

        $session = $request->hasSession() ? $request->session() : null;

        if ($ua['is_bot']) {
            $source = ['channel' => SourceClassifier::BOT, 'name' => null];
            $isEntry = false;
        } else {
            $source = SourceClassifier::classify($refHost ?: null, $utm('utm_source'), $utm('utm_medium'), $request->getHost());
            $isEntry = $session !== null && ! $session->has(self::SESSION_ENTRY);
            if ($isEntry) {
                $session->put(self::SESSION_ENTRY, true);
                // Primer origen de la sesión: se guarda con voluntarios y donaciones
                if ($source['channel'] !== SourceClassifier::INTERNAL) {
                    $session->put(self::SESSION_SOURCE, mb_substr(SourceClassifier::label($source), 0, 100));
                }
            }
        }

        $vid = $request->cookie('lr_vid');
        $country = strtoupper((string) $request->header('CF-IPCountry'));

        Visit::create([
            'session_hash' => $session ? hash('sha256', $session->getId()) : null,
            'visitor_id' => is_string($vid) && preg_match('/^[A-Za-z0-9-]{8,40}$/', $vid) ? $vid : null,
            'is_entry' => $isEntry,
            'path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 255),
            'route' => mb_substr((string) $request->route()?->getName(), 0, 80) ?: null,
            'status' => $response->getStatusCode(),
            'referrer_host' => $refHost ? mb_substr(SourceClassifier::normalizeHost($refHost), 0, 190) : null,
            'referrer' => $refUrl !== '' ? mb_substr($refUrl, 0, 500) : null,
            'channel' => $source['channel'],
            'source_name' => $source['name'],
            'utm_source' => $utm('utm_source'),
            'utm_medium' => $utm('utm_medium'),
            'utm_campaign' => $utm('utm_campaign'),
            'ip' => $request->ip(),
            'country' => preg_match('/^[A-Z]{2}$/', $country) && $country !== 'XX' ? $country : null,
            'language' => mb_substr((string) $request->getPreferredLanguage(), 0, 10) ?: null,
            'device' => $ua['device'],
            'browser' => $ua['browser'],
            'os' => $ua['os'],
            'is_bot' => $ua['is_bot'],
            'is_ai_bot' => $ua['ai_bot'],
            'bot_name' => $ua['bot_name'],
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500) ?: null,
        ]);
    }
}

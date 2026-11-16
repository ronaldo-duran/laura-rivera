<?php

namespace App\Http\Controllers;

use App\Models\VisitEvent;
use App\Support\Analytics\UserAgent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Recibe clics, desplazamiento y tiempo en página que envía site.js con
 * navigator.sendBeacon. Solo guarda si el visitante aceptó las cookies de medición.
 */
class MetricsEventController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if ($request->cookie('lr_consent') !== 'all' || UserAgent::parse($request->userAgent())['is_bot']) {
            return response()->noContent();
        }

        $payload = json_decode($request->getContent(), true);
        $events = is_array($payload['events'] ?? null) ? array_slice($payload['events'], 0, 20) : [];

        $vid = $request->cookie('lr_vid');
        $base = [
            'session_hash' => $request->hasSession() ? hash('sha256', $request->session()->getId()) : null,
            'visitor_id' => is_string($vid) && preg_match('/^[A-Za-z0-9-]{8,40}$/', $vid) ? $vid : null,
            'created_at' => now(),
        ];

        $rows = [];
        foreach ($events as $event) {
            $v = Validator::make(is_array($event) ? $event : [], [
                'type' => ['required', 'string', 'in:'.implode(',', VisitEvent::TYPES)],
                'label' => ['nullable', 'string', 'max:500'],
                'target' => ['nullable', 'string', 'max:2000'],
                'path' => ['nullable', 'string', 'max:2000'],
                'value' => ['nullable', 'integer', 'min:0', 'max:86400'],
            ]);
            if ($v->fails()) {
                continue;
            }
            $e = $v->validated();
            $rows[] = $base + [
                'type' => $e['type'],
                'label' => isset($e['label']) ? mb_substr(trim(preg_replace('/\s+/', ' ', $e['label'])), 0, 150) : null,
                'target' => isset($e['target']) ? mb_substr($e['target'], 0, 500) : null,
                'path' => isset($e['path']) ? mb_substr($e['path'], 0, 255) : null,
                'value' => $e['value'] ?? null,
            ];
        }

        if ($rows) {
            VisitEvent::insert($rows);
        }

        return response()->noContent();
    }
}

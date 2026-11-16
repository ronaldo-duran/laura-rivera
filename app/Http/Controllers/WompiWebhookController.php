<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\Wompi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Recibe los eventos de Wompi (transaction.updated). Es la fuente oficial del
 * estado de cada donación. Responde 200 cuando el evento es válido aunque no
 * cambie nada, para que Wompi no reintente.
 */
class WompiWebhookController extends Controller
{
    public function __invoke(Request $request, Wompi $wompi): JsonResponse
    {
        $payload = $request->json()->all();

        if (! $wompi->verifyEvent($payload, $request->header('X-Event-Checksum'))) {
            Log::warning('Wompi: evento con firma inválida', ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 401);
        }

        if (($payload['event'] ?? null) !== 'transaction.updated') {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $tx = Arr::get($payload, 'data.transaction', []);
        $donation = Donation::where('reference', $tx['reference'] ?? '')->first();

        if (! $donation) {
            Log::notice('Wompi: evento de una referencia desconocida', ['reference' => $tx['reference'] ?? null]);

            return response()->json(['ok' => true, 'unknown_reference' => true]);
        }

        if ((int) ($tx['amount_in_cents'] ?? -1) !== $donation->amount_in_cents || ($tx['currency'] ?? 'COP') !== $donation->currency) {
            Log::error('Wompi: el monto del evento no coincide con la donación', ['reference' => $donation->reference]);

            return response()->json(['ok' => false, 'amount_mismatch' => true], 422);
        }

        $donation->applyWompiStatus((string) ($tx['status'] ?? ''), $tx, $payload);

        return response()->json(['ok' => true]);
    }
}

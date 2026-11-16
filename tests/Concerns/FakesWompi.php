<?php

namespace Tests\Concerns;

use Illuminate\Support\Arr;

trait FakesWompi
{
    protected string $integritySecret = 'test_integrity_fixture';

    protected string $eventsSecret = 'test_events_fixture';

    protected function configureWompi(array $overrides = []): void
    {
        config(['services.wompi' => array_merge(config('services.wompi'), [
            'env' => 'sandbox',
            'public_key' => 'pub_test_fixture',
            'private_key' => 'prv_test_fixture',
            'integrity_secret' => $this->integritySecret,
            'events_secret' => $this->eventsSecret,
            'api_url' => 'https://sandbox.wompi.co/v1',
        ], $overrides)]);
    }

    /** Evento firmado igual que Wompi: SHA256(propiedades + timestamp + secreto). */
    protected function wompiEvent(array $transaction, ?string $secret = null, int $timestamp = 1763280000): array
    {
        $properties = ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'];
        $data = ['transaction' => $transaction];

        $concat = implode('', array_map(fn ($p) => (string) Arr::get($data, $p), $properties));

        return [
            'event' => 'transaction.updated',
            'data' => $data,
            'environment' => 'test',
            'signature' => [
                'properties' => $properties,
                'checksum' => hash('sha256', $concat.$timestamp.($secret ?? $this->eventsSecret)),
            ],
            'timestamp' => $timestamp,
            'sent_at' => '2026-11-16T10:00:00.000Z',
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integración con Wompi (Colombia) mediante Web Checkout.
 * Docs: https://docs.wompi.co (Widget & Checkout Web, Eventos, Transacciones).
 */
class Wompi
{
    public function __construct(private readonly array $config) {}

    public static function fromConfig(): self
    {
        return new self(config('services.wompi'));
    }

    public function isConfigured(): bool
    {
        return filled($this->config['public_key']) && filled($this->config['integrity_secret']);
    }

    public function isSandbox(): bool
    {
        return ($this->config['env'] ?? 'sandbox') !== 'production';
    }

    /** Firma de integridad: SHA256(referencia + monto en centavos + moneda + secreto de integridad). */
    public function integritySignature(string $reference, int $amountInCents, string $currency): string
    {
        return hash('sha256', $reference.$amountInCents.$currency.$this->config['integrity_secret']);
    }

    /** URL del Web Checkout de Wompi con los datos del aportante precargados. */
    public function checkoutUrl(Donation $donation, string $redirectUrl): string
    {
        $legalIdTypes = ['CC' => 'CC', 'CE' => 'CE', 'PA' => 'PP', 'TI' => 'TI'];

        $params = array_filter([
            'public-key' => $this->config['public_key'],
            'currency' => $donation->currency,
            'amount-in-cents' => $donation->amount_in_cents,
            'reference' => $donation->reference,
            'signature:integrity' => $this->integritySignature($donation->reference, $donation->amount_in_cents, $donation->currency),
            'redirect-url' => $redirectUrl,
            'customer-data:email' => $donation->email,
            'customer-data:full-name' => $donation->full_name,
            'customer-data:legal-id' => isset($legalIdTypes[$donation->document_type]) ? $donation->document_number : null,
            'customer-data:legal-id-type' => $legalIdTypes[$donation->document_type] ?? null,
            'customer-data:phone-number' => $donation->phone && ! str_starts_with($donation->phone, '+') ? $donation->phone : null,
            'customer-data:phone-number-prefix' => $donation->phone && ! str_starts_with($donation->phone, '+') ? '+57' : null,
        ], fn ($v) => $v !== null && $v !== '');

        return rtrim($this->config['checkout_url'], '/').'/?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Verifica un evento: SHA256(valores de signature.properties + timestamp + secreto de eventos).
     * Las propiedades se leen del propio evento porque Wompi puede cambiarlas.
     */
    public function verifyEvent(array $payload, ?string $headerChecksum = null): bool
    {
        $secret = $this->config['events_secret'] ?? null;
        $properties = Arr::get($payload, 'signature.properties');
        $timestamp = Arr::get($payload, 'timestamp', Arr::get($payload, 'signature.timestamp'));
        $checksum = $headerChecksum ?: Arr::get($payload, 'signature.checksum');

        if (blank($secret) || ! is_array($properties) || blank($timestamp) || blank($checksum)) {
            return false;
        }

        $concat = '';
        foreach ($properties as $path) {
            $concat .= (string) Arr::get($payload['data'] ?? [], $path, '');
        }

        $expected = hash('sha256', $concat.$timestamp.$secret);

        return hash_equals(strtolower($expected), strtolower((string) $checksum));
    }

    /** Consulta una transacción en la API (requiere llave privada). Devuelve null si no se puede. */
    public function fetchTransaction(string $id): ?array
    {
        if (blank($this->config['private_key'] ?? null) || ! preg_match('/^[\w-]{5,64}$/', $id)) {
            return null;
        }

        try {
            $response = Http::withToken($this->config['private_key'])
                ->acceptJson()->timeout(8)
                ->get(rtrim($this->config['api_url'], '/').'/transactions/'.$id);

            return $response->successful() ? $response->json('data') : null;
        } catch (\Throwable $e) {
            Log::warning('Wompi: no se pudo consultar la transacción', ['id' => $id, 'error' => $e->getMessage()]);

            return null;
        }
    }
}

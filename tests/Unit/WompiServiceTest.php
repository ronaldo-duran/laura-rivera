<?php

namespace Tests\Unit;

use App\Models\Donation;
use App\Services\Wompi;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesWompi;
use Tests\TestCase;

class WompiServiceTest extends TestCase
{
    use FakesWompi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureWompi();
    }

    public function test_integrity_signature_follows_wompi_formula(): void
    {
        $sig = app(Wompi::class)->integritySignature('LR-1', 5000000, 'COP');

        $this->assertSame(hash('sha256', 'LR-15000000COP'.$this->integritySecret), $sig);
        $this->assertSame(64, strlen($sig));
    }

    public function test_checkout_url_contains_required_parameters(): void
    {
        $donation = new Donation([
            'reference' => 'LR-REF-1', 'amount_in_cents' => 5000000, 'currency' => 'COP',
            'full_name' => 'Ana Pérez', 'document_type' => 'CC', 'document_number' => '1090123456',
            'email' => 'ana@example.com', 'phone' => '3001234567',
        ]);

        $url = app(Wompi::class)->checkoutUrl($donation, 'https://laura.test/donar/resultado');
        $this->assertStringStartsWith('https://checkout.wompi.co/p/?', $url);

        parse_str(parse_url($url, PHP_URL_QUERY), $q);
        $this->assertSame('pub_test_fixture', $q['public-key']);
        $this->assertSame('COP', $q['currency']);
        $this->assertSame('5000000', $q['amount-in-cents']);
        $this->assertSame('LR-REF-1', $q['reference']);
        $this->assertSame(hash('sha256', 'LR-REF-15000000COP'.$this->integritySecret), $q['signature:integrity']);
        $this->assertSame('https://laura.test/donar/resultado', $q['redirect-url']);
        $this->assertSame('ana@example.com', $q['customer-data:email']);
        $this->assertSame('CC', $q['customer-data:legal-id-type']);
        $this->assertSame('1090123456', $q['customer-data:legal-id']);
        $this->assertSame('+57', $q['customer-data:phone-number-prefix']);
    }

    public function test_valid_event_signature_is_accepted(): void
    {
        $event = $this->wompiEvent(['id' => '1-2-3', 'status' => 'APPROVED', 'amount_in_cents' => 5000000]);

        $this->assertTrue(app(Wompi::class)->verifyEvent($event));
        $this->assertTrue(app(Wompi::class)->verifyEvent($event, strtoupper($event['signature']['checksum'])));
    }

    public function test_tampered_or_wrongly_signed_events_are_rejected(): void
    {
        $wompi = app(Wompi::class);

        $event = $this->wompiEvent(['id' => '1-2-3', 'status' => 'DECLINED', 'amount_in_cents' => 5000000]);
        $event['data']['transaction']['status'] = 'APPROVED';
        $this->assertFalse($wompi->verifyEvent($event));

        $other = $this->wompiEvent(['id' => '1-2-3', 'status' => 'APPROVED', 'amount_in_cents' => 5000000], 'otro_secreto');
        $this->assertFalse($wompi->verifyEvent($other));

        $this->assertFalse($wompi->verifyEvent(['event' => 'transaction.updated']));
    }

    public function test_events_are_rejected_when_secret_is_not_configured(): void
    {
        $this->configureWompi(['events_secret' => null]);
        $event = $this->wompiEvent(['id' => '1', 'status' => 'APPROVED', 'amount_in_cents' => 100]);

        $this->assertFalse(app(Wompi::class)->verifyEvent($event));
    }

    public function test_fetch_transaction_uses_private_key(): void
    {
        Http::fake(['sandbox.wompi.co/v1/transactions/*' => Http::response(['data' => ['id' => '12345-1763280000-49201', 'status' => 'APPROVED']])]);

        $tx = app(Wompi::class)->fetchTransaction('12345-1763280000-49201');

        $this->assertSame('APPROVED', $tx['status']);
        Http::assertSent(fn ($r) => $r->url() === 'https://sandbox.wompi.co/v1/transactions/12345-1763280000-49201'
            && $r->hasHeader('Authorization', 'Bearer prv_test_fixture'));
    }

    public function test_fetch_transaction_without_private_key_or_with_bad_id_returns_null(): void
    {
        Http::fake();
        $this->assertNull(app(Wompi::class)->fetchTransaction('../../etc'));

        $this->configureWompi(['private_key' => null]);
        $this->assertNull(app(Wompi::class)->fetchTransaction('12345-1763280000-49201'));
        Http::assertNothingSent();
    }
}

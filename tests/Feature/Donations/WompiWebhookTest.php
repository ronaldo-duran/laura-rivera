<?php

namespace Tests\Feature\Donations;

use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\FakesWompi;
use Tests\TestCase;

class WompiWebhookTest extends TestCase
{
    use FakesWompi, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureWompi();
    }

    private function tx(Donation $d, string $status, array $extra = []): array
    {
        return array_merge([
            'id' => '1234-1610641025-49201',
            'amount_in_cents' => $d->amount_in_cents,
            'reference' => $d->reference,
            'customer_email' => $d->email,
            'currency' => 'COP',
            'payment_method_type' => 'PSE',
            'status' => $status,
        ], $extra);
    }

    public function test_approved_event_marks_donation_paid(): void
    {
        $d = Donation::factory()->create();

        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'APPROVED')))->assertOk();

        $d->refresh();
        $this->assertSame('APPROVED', $d->status);
        $this->assertSame('1234-1610641025-49201', $d->wompi_transaction_id);
        $this->assertSame('PSE', $d->payment_method_type);
        $this->assertNotNull($d->paid_at);
        $this->assertSame('transaction.updated', $d->last_event['event']);
    }

    public function test_webhook_does_not_need_csrf_token(): void
    {
        $d = Donation::factory()->create();

        // post() (no JSON helper) para simular una petición externa real sin sesión
        $this->call('POST', '/webhooks/wompi', [], [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode($this->wompiEvent($this->tx($d, 'APPROVED'))))->assertOk();
    }

    public function test_invalid_signature_is_rejected_and_nothing_changes(): void
    {
        $d = Donation::factory()->create();
        $event = $this->wompiEvent($this->tx($d, 'APPROVED'), 'secreto_falso');

        $this->postJson('/webhooks/wompi', $event)->assertStatus(401);
        $this->assertSame('CREATED', $d->fresh()->status);
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        $d = Donation::factory()->create(['amount_in_cents' => 5000000]);

        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'APPROVED', ['amount_in_cents' => 100])))
            ->assertStatus(422);
        $this->assertSame('CREATED', $d->fresh()->status);
    }

    public function test_unknown_reference_returns_200_so_wompi_does_not_retry(): void
    {
        $this->postJson('/webhooks/wompi', $this->wompiEvent([
            'id' => '1', 'reference' => 'NO-EXISTE', 'status' => 'APPROVED', 'amount_in_cents' => 100,
        ]))->assertOk()->assertJson(['unknown_reference' => true]);
    }

    public function test_repeated_and_out_of_order_events_do_not_downgrade_status(): void
    {
        $d = Donation::factory()->create();

        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'APPROVED')))->assertOk();
        $paidAt = $d->fresh()->paid_at;

        $this->travel(5)->minutes();
        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'PENDING')))->assertOk();
        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'APPROVED')))->assertOk();

        $d->refresh();
        $this->assertSame('APPROVED', $d->status);
        $this->assertEquals($paidAt, $d->paid_at);
    }

    public function test_pending_then_declined(): void
    {
        $d = Donation::factory()->create();

        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'PENDING')));
        $this->assertSame('PENDING', $d->fresh()->status);

        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'DECLINED')));
        $this->assertSame('DECLINED', $d->fresh()->status);
        $this->assertNull($d->fresh()->paid_at);
    }

    public function test_approved_can_be_voided(): void
    {
        $d = Donation::factory()->approved()->create();

        $this->postJson('/webhooks/wompi', $this->wompiEvent($this->tx($d, 'VOIDED')))->assertOk();
        $this->assertSame('VOIDED', $d->fresh()->status);
    }

    public function test_other_event_types_are_acknowledged_and_ignored(): void
    {
        $event = $this->wompiEvent(['id' => '1', 'status' => 'APPROVED', 'amount_in_cents' => 1]);
        $event['event'] = 'nequi_token.updated';
        // re-firmar no es necesario: la firma cubre data, no el nombre del evento
        $this->postJson('/webhooks/wompi', $event)->assertOk()->assertJson(['ignored' => true]);
    }
}

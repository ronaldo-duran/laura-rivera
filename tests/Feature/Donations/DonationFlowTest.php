<?php

namespace Tests\Feature\Donations;

use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesWompi;
use Tests\TestCase;

class DonationFlowTest extends TestCase
{
    use FakesWompi, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureWompi();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'amount' => '50000',
            'full_name' => 'Ana Pérez',
            'document_type' => 'CC',
            'document_number' => '1.090.123.456',
            'email' => 'ANA@example.com',
            'phone' => '300 123 4567',
            'accept_terms' => '1',
        ], $overrides);
    }

    public function test_donation_page_and_home_cta(): void
    {
        $this->get('/donar')->assertOk()->assertSee('Continuar al pago seguro')->assertSee('Modo de pruebas');
        $this->get('/')->assertOk()->assertSee('Hacer un aporte');
    }

    public function test_page_explains_when_wompi_is_not_configured(): void
    {
        $this->configureWompi(['public_key' => null]);

        $this->get('/donar')->assertOk()->assertSee('estarán disponibles muy pronto')->assertDontSee('Continuar al pago seguro');
        $this->post('/donar', $this->validData())->assertStatus(503);
    }

    public function test_store_creates_donation_and_redirects_to_wompi(): void
    {
        $response = $this->post('/donar', $this->validData());

        $donation = Donation::sole();
        $this->assertSame('CREATED', $donation->status);
        $this->assertSame(5000000, $donation->amount_in_cents);
        $this->assertSame('1090123456', $donation->document_number);
        $this->assertSame('ana@example.com', $donation->email);
        $this->assertStringStartsWith('LR-', $donation->reference);

        $location = $response->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith('https://checkout.wompi.co/p/?', $location);
        $this->assertStringContainsString('reference='.$donation->reference, $location);
        $this->assertStringContainsString('amount-in-cents=5000000', $location);
    }

    public function test_custom_amount_is_parsed(): void
    {
        $this->post('/donar', $this->validData(['amount' => 'otro', 'custom_amount' => '$ 80.000']));

        $this->assertSame(8000000, Donation::sole()->amount_in_cents);
    }

    public function test_amount_limits(): void
    {
        $this->post('/donar', $this->validData(['amount' => 'otro', 'custom_amount' => '1000']))->assertSessionHasErrors('amount_value');
        $this->post('/donar', $this->validData(['amount' => 'otro', 'custom_amount' => '999999999']))->assertSessionHasErrors('amount_value');
        $this->post('/donar', $this->validData(['amount' => 'otro', 'custom_amount' => '']))->assertSessionHasErrors('amount_value');
        $this->assertSame(0, Donation::count());
    }

    public function test_identity_and_terms_are_required(): void
    {
        $this->post('/donar', $this->validData(['full_name' => '', 'document_number' => '', 'email' => 'x', 'accept_terms' => null]))
            ->assertSessionHasErrors(['full_name', 'document_number', 'email', 'accept_terms']);
    }

    public function test_amount_can_be_preselected_from_link(): void
    {
        $this->get('/donar?monto=100000')->assertSee('value="100000" checked', false);
    }

    public function test_result_page_confirms_approved_payment_via_api(): void
    {
        $donation = Donation::factory()->create(['amount_in_cents' => 5000000]);
        Http::fake(['sandbox.wompi.co/v1/transactions/*' => Http::response(['data' => [
            'id' => '111-222', 'reference' => $donation->reference, 'status' => 'APPROVED',
            'amount_in_cents' => 5000000, 'payment_method_type' => 'NEQUI',
        ]])]);

        $this->get('/donar/resultado?id=111-222')->assertOk()->assertSee('¡Gracias por su aporte de $50.000!');

        $donation->refresh();
        $this->assertSame('APPROVED', $donation->status);
        $this->assertSame('NEQUI', $donation->payment_method_type);
        $this->assertNotNull($donation->paid_at);
    }

    public function test_result_page_ignores_api_amount_mismatch(): void
    {
        $donation = Donation::factory()->create(['amount_in_cents' => 5000000]);
        Http::fake(['*' => Http::response(['data' => [
            'id' => '111-222', 'reference' => $donation->reference, 'status' => 'APPROVED', 'amount_in_cents' => 100,
        ]])]);

        $this->get('/donar/resultado?id=111-222')->assertOk()->assertSee('Estamos confirmando su pago');
        $this->assertSame('CREATED', $donation->fresh()->status);
    }

    public function test_result_page_without_id_shows_processing(): void
    {
        $this->get('/donar/resultado')->assertOk()->assertSee('Estamos confirmando su pago');
    }
}

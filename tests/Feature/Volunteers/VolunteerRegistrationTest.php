<?php

namespace Tests\Feature\Volunteers;

use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VolunteerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'document_type' => 'CC',
            'document_number' => '1.090.123.456',
            'first_names' => 'María  José',
            'last_names' => 'Gómez Peñaranda',
            'preferred_name' => 'Majo',
            'treatment' => 'senora',
            'email' => '  MARIA@Example.com ',
            'phone' => '+57 300 123 4567',
            'neighborhood' => 'La Parada',
            'help_type' => 'Puerta a puerta',
            'accept_terms' => '1',
        ], $overrides);
    }

    public function test_form_is_on_its_own_page_and_on_home(): void
    {
        $this->get('/voluntarios')->assertOk()->assertSee('name="document_number"', false)->assertSee('Cédula de ciudadanía');
        $this->get('/')->assertOk()->assertSee('id="registro"', false);
    }

    public function test_registration_saves_normalized_data(): void
    {
        $this->post('/voluntarios', $this->validData())->assertRedirect(route('volunteers.thanks'));

        $v = Volunteer::sole();
        $this->assertSame('CC', $v->document_type);
        $this->assertSame('1090123456', $v->document_number);
        $this->assertSame('María José', $v->first_names);
        $this->assertSame('maria@example.com', $v->email);
        $this->assertSame('3001234567', $v->phone);
        $this->assertSame('senora', $v->treatment);
        $this->assertNotNull($v->terms_accepted_at);
        $this->assertSame(config('laura.terminos_version'), $v->terms_version);
        $this->assertNotNull($v->ip_address);
    }

    public function test_thank_you_page_greets_with_preferred_treatment(): void
    {
        $this->followingRedirects()->post('/voluntarios', $this->validData())
            ->assertOk()->assertSee('Señora Majo');
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->post('/voluntarios', $this->validData(['accept_terms' => null]))
            ->assertSessionHasErrors('accept_terms');
        $this->assertSame(0, Volunteer::count());
    }

    public static function requiredFields(): array
    {
        return [['document_type'], ['document_number'], ['first_names'], ['last_names'], ['treatment'], ['email'], ['phone']];
    }

    #[DataProvider('requiredFields')]
    public function test_required_fields(string $field): void
    {
        $this->post('/voluntarios', $this->validData([$field => '']))->assertSessionHasErrors($field);
    }

    public function test_preferred_name_neighborhood_and_help_type_are_optional(): void
    {
        $this->post('/voluntarios', $this->validData(['preferred_name' => '', 'neighborhood' => '', 'help_type' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull(Volunteer::sole()->preferred_name);
    }

    public static function invalidValues(): array
    {
        return [
            'tipo de documento inventado' => ['document_type', 'XX'],
            'cédula con letras' => ['document_number', 'ABC123'],
            'cédula muy corta' => ['document_number', '123'],
            'correo inválido' => ['email', 'no-es-correo'],
            'celular fijo' => ['phone', '6075551234'],
            'celular corto' => ['phone', '300123'],
            'trato inventado' => ['treatment', 'doctor'],
            'nombre con números' => ['first_names', 'Ana123'],
            'barrio inventado' => ['neighborhood', 'Narnia'],
        ];
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(string $field, string $value): void
    {
        $this->post('/voluntarios', $this->validData([$field => $value]))->assertSessionHasErrors($field);
    }

    public function test_passport_accepts_letters(): void
    {
        $this->post('/voluntarios', $this->validData(['document_type' => 'PA', 'document_number' => 'ab-123456']))
            ->assertSessionHasNoErrors();
        $this->assertSame('AB123456', Volunteer::sole()->document_number);
    }

    public function test_international_phone_is_accepted(): void
    {
        $this->post('/voluntarios', $this->validData(['phone' => '+58 414 123 4567']))->assertSessionHasNoErrors();
        $this->assertSame('+584141234567', Volunteer::sole()->phone);
    }

    public function test_same_document_cannot_register_twice(): void
    {
        $this->post('/voluntarios', $this->validData());
        $this->post('/voluntarios', $this->validData(['email' => 'otro@example.com', 'document_number' => '1090123456']))
            ->assertSessionHasErrors(['document_number' => 'Este documento ya está registrado como voluntario. ¡Gracias por su apoyo!']);

        $this->assertSame(1, Volunteer::count());
    }

    public function test_same_number_with_different_document_type_is_allowed(): void
    {
        $this->post('/voluntarios', $this->validData());
        $this->post('/voluntarios', $this->validData(['document_type' => 'TI']))->assertSessionHasNoErrors();

        $this->assertSame(2, Volunteer::count());
    }

    public function test_validation_errors_return_to_the_form_anchor(): void
    {
        $this->from('/')->post('/voluntarios', $this->validData(['email' => '']))
            ->assertRedirect(url('/').'#registro');
    }

    public function test_honeypot_silently_discards_bots(): void
    {
        $this->post('/voluntarios', $this->validData(['website' => 'http://spam.example']))
            ->assertRedirect(route('volunteers.thanks'));

        $this->assertSame(0, Volunteer::count());
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/voluntarios', $this->validData(['document_number' => (string) (10000000 + $i), 'email' => "p{$i}@example.com"]));
        }

        $this->post('/voluntarios', $this->validData(['document_number' => '99999999']))->assertStatus(429);
    }

    public function test_thanks_page_is_not_indexed(): void
    {
        $this->get('/voluntarios/gracias')->assertOk()->assertSee('<meta name="robots" content="noindex">', false);
    }
}

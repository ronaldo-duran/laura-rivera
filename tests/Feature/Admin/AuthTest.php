<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_login_page_is_not_indexed(): void
    {
        $this->get('/login')->assertOk()->assertSee('noindex, nofollow', false);
    }

    public function test_admin_can_log_in_and_last_login_is_recorded(): void
    {
        $user = $this->admin(['email' => 'laura@example.com', 'password' => 'Secreta12345']);

        $this->post('/login', ['email' => 'laura@example.com', 'password' => 'Secreta12345'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->admin(['email' => 'laura@example.com', 'password' => 'Secreta12345']);

        $this->from('/login')->post('/login', ['email' => 'laura@example.com', 'password' => 'otra'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $this->admin(['email' => 'off@example.com', 'password' => 'Secreta12345', 'active' => false]);

        $this->post('/login', ['email' => 'off@example.com', 'password' => 'Secreta12345'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_user_is_logged_out_on_next_request(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->get('/admin')->assertOk();

        $user->update(['active' => false]);

        $this->actingAs($user->fresh())->get('/admin')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $this->admin(['email' => 'laura@example.com', 'password' => 'Secreta12345']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'laura@example.com', 'password' => 'mala']);
        }

        $this->post('/login', ['email' => 'laura@example.com', 'password' => 'Secreta12345'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_user_without_role_cannot_enter_panel(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_logout(): void
    {
        $this->actingAs($this->admin())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_logged_in_user_visiting_login_goes_to_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/login')->assertRedirect(route('admin.dashboard'));
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_admin(): void
    {
        $this->artisan('app:crear-admin', [
            '--name' => 'Laura Rivera', '--email' => 'laura@example.com', '--password' => 'Segura12345',
        ])->assertSuccessful();

        $user = User::where('email', 'laura@example.com')->firstOrFail();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->active);
    }

    public function test_command_promotes_existing_user(): void
    {
        User::factory()->create(['email' => 'ya@example.com']);

        $this->artisan('app:crear-admin', [
            '--name' => 'Ya Existe', '--email' => 'ya@example.com', '--password' => 'Segura12345',
        ])->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertTrue(User::first()->isAdmin());
    }

    public function test_command_rejects_weak_password(): void
    {
        $this->artisan('app:crear-admin', [
            '--name' => 'X', '--email' => 'x@example.com', '--password' => '123',
        ])->assertFailed();

        $this->assertSame(0, User::count());
    }
}

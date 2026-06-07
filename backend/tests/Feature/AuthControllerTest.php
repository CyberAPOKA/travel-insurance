<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_registers_a_user_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'ana@example.com')
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'ana@example.com',
        ]);
    }

    #[Test]
    public function it_logs_in_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'ana@example.com')
            ->assertJsonStructure(['token']);
    }

    #[Test]
    public function it_rejects_invalid_login_credentials(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}

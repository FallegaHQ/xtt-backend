<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase{
    use RefreshDatabase;

    #[Test]
    public function shouldRegisterCreatesNewUser(): void{
        $userData = [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                                           'status',
                                           'message',
                                           'user'          => [
                                               'id',
                                               'name',
                                               'email',
                                           ],
                                           'authorization' => [
                                               'token',
                                               'type',
                                           ],
                                       ]);

        // Check that the user was created in the database
        $this->assertDatabaseHas('users', [
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function shouldRegisterValidatesInput(): void{
        // Empty request
        $response = $this->postJson('/api/v1/auth/register', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(
                     [
                         'name',
                         'email',
                         'password',
                     ],
                 );

        // Invalid email
        $response = $this->postJson('/api/v1/auth/register', [
            'name'     => 'Test User',
            'email'    => 'invalid-email',
            'password' => 'password123',
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

        // Password too short
        $response = $this->postJson('/api/v1/auth/register', [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'short',
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function shouldRegisterRequiresUniqueEmail(): void{
        // Create a user first
        User::factory()
            ->create([
                         'email' => 'existing@example.com',
                     ]);

        // Try to register with the same email
        $response = $this->postJson('/api/v1/auth/register', [
            'name'     => 'Another User',
            'email'    => 'existing@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function shouldLoginWithValidCredentials(): void{
        // Create a user
        $user = User::factory()
                    ->create([
                                 'email'    => 'test@example.com',
                                 'password' => bcrypt('password123'),
                             ]);

        // Attempt login
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                                           'status',
                                           'user'          => [
                                               'id',
                                               'name',
                                               'email',
                                           ],
                                           'authorization' => [
                                               'token',
                                               'type',
                                           ],
                                       ]);
    }

    #[Test]
    public function shouldLoginWithInvalidCredentials(): void{
        // Create a user
        $user = User::factory()
                    ->create([
                                 'email'    => 'test@example.com',
                                 'password' => bcrypt('password123'),
                             ]);

        // Attempt login with wrong password
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                                  'status'  => 'error',
                                  'message' => 'Unauthorized',
                              ]);
    }

    #[Test]
    public function shouldLoginValidatesInput(): void{
        // Empty request
        $response = $this->postJson('/api/v1/auth/login', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(
                     [
                         'email',
                         'password',
                     ],
                 );

        // Invalid email
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'invalid-email',
            'password' => 'password123',
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function shouldLogout(): void{
        // Create and authenticate a user
        $user = User::factory()
                    ->create();
        $token = \JWTAuth::fromUser($user);

        $response = $this->actingAs($user)
                         ->postJson('/api/v1/auth/logout?token=' . $token);

        $response->assertStatus(200)
                 ->assertJson([
                                  'status'  => 'success',
                                  'message' => 'Successfully logged out',
                              ]);
    }

    #[Test]
    public function shouldMeReturnsAuthenticatedUser(): void{
        // Create and authenticate a user
        $user = User::factory()
                    ->create();

        $response = $this->actingAs($user)
                         ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                 ->assertJson([
                                  'id'    => $user->id,
                                  'name'  => $user->name,
                                  'email' => $user->email,
                              ]);
    }

    #[Test]
    public function shouldRefreshToken(): void{
        // Create and authenticate a user
        $user = User::factory()
                    ->create();
        $token = \JWTAuth::fromUser($user);

        $response = $this->actingAs($user)
                         ->postJson('/api/v1/auth/refresh?token=' . $token);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                                           'status',
                                           'user',
                                           'authorization' => [
                                               'token',
                                               'type',
                                           ],
                                       ]);
    }
}
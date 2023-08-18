<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRegister()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'User successfully registered']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }
    public function testLoginAndLogout()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                ]);

        // Extract the access token from the response JSON
        $accessToken = $response['access_token'];

        // Use the access token to perform logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/auth/logout');

        $logoutResponse->assertStatus(201)
                    ->assertExactJson(['message' => 'User logged out']);
    }


    public function testProfile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/auth/profile');

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at']);
    }
}

<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_authenticated_request_passes(): void
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $response = $this->getJson('/api/users', [
            'Authorization' => "Bearer $token",
        ]);

        // May be 403 (no role) but NOT 401
        $this->assertNotEquals(401, $response->getStatusCode());
    }
}

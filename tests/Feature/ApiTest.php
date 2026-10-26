<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_codelist_endpoint_returns_json(): void
    {
        $response = $this->get('/api/codelist/en');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $this->assertIsArray($response->json());
    }

    public function test_api_login_returns_token_for_valid_user(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin@nrgi.app',
            'password' => 'admin123',
        ]);

        $response->assertOk()->assertJsonPath('status', 'success')->assertJsonPath('is_admin', true);
    }
}

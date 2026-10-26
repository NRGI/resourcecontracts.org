<?php

namespace Tests\Feature;

use App\Nrgi\Entities\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Resource Contracts');
        $this->get('/')->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
        $this->get('/contract')->assertRedirect('/login');
    }

    public function test_admin_can_login_with_seeded_credentials(): void
    {
        $response = $this->post('/login', [
            'email'    => 'admin@nrgi.app',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs(User::where('email', 'admin@nrgi.app')->first());
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::where('email', 'researcher@nrgi.app')->first();
        $user->status = 'false';
        $user->save();

        $response = $this->post('/login', [
            'email'    => 'researcher@nrgi.app',
            'password' => 'researcher123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $response = $this->from('/login')->post('/login', ['email' => 'admin@nrgi.app', 'password' => 'nope']);

        $response->assertRedirect('/login');
        $this->assertTrue(
            session()->has('errors') || session()->has('error'),
            'A failed login must flash an error message'
        );
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('email', 'admin@nrgi.app')->first();
        $this->actingAs($user)->get('/logout')->assertRedirect();
        $this->assertGuest();
    }
}

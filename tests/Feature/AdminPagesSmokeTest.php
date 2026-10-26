<?php

namespace Tests\Feature;

use App\Nrgi\Entities\User\User;
use App\Nrgi\Mturk\Services\MTurkService;
use App\Nrgi\Services\Abby\AbbyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke tests: every main admin page must render for a superadmin.
 */
class AdminPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    protected function admin(): User
    {
        return User::where('email', 'admin@nrgi.app')->firstOrFail();
    }

    public function test_dashboard_renders(): void
    {
        $this->mock(MTurkService::class, function ($mock) {
            $mock->shouldReceive('getBalance')->andReturn('0.00');
        });
        $this->mock(AbbyService::class, function ($mock) {
            $mock->shouldReceive('getApplicationInfo')->andReturn(['pages' => 'N/A']);
        });

        $this->actingAs($this->admin())->get('/home')->assertOk();
    }

    #[DataProvider('pages')]
    public function test_admin_pages_render(string $uri): void
    {
        $this->actingAs($this->admin())->get($uri)->assertOk();
    }

    public static function pages(): array
    {
        return [
            ['/contract'],
            ['/contract/create'],
            ['/contract/import'],
            ['/user'],
            ['/user/create'],
            ['/profile'],
            ['/profile/edit'],
            ['/activities'],
            ['/mturk'],
            ['/mturk/tasks'],
            ['/mturk/activity'],
            ['/quality'],
            ['/disclosure'],
            ['/utility'],
            ['/logs'],
        ];
    }

    public function test_user_list_shows_seeded_users(): void
    {
        $this->actingAs($this->admin())->get('/user')
            ->assertOk()
            ->assertSee('admin@nrgi.app')
            ->assertSee('researcher@nrgi.app');
    }

    public function test_researcher_cannot_open_user_management(): void
    {
        $researcher = User::where('email', 'researcher@nrgi.app')->firstOrFail();

        $response = $this->actingAs($researcher)->get('/user');
        $this->assertContains($response->getStatusCode(), [302, 403], 'Researcher must not see user management');
    }
}

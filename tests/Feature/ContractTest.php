<?php

namespace Tests\Feature;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractTest extends TestCase
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

    protected function makeContract(array $overrides = []): Contract
    {
        $this->seed(\Database\Seeders\ResourcesTableSeeder::class);
        $this->seed(\Database\Seeders\ContractTypeTableSeeder::class);
        $this->seed(\Database\Seeders\DocumentTypeTableSeeder::class);

        $metadata = config('metadata.schema')['metadata'];
        $metadata['contract_name']   = 'Smoke Test Contract';
        $metadata['language']        = 'en';
        $metadata['category']        = ['rc'];
        $metadata['resource']        = ['Acacia'];
        $metadata['countries']       = [['code' => 'MN', 'name' => 'Mongolia']];
        $metadata['signature_year']  = '2020';
        $metadata['signature_date']  = '2020-01-01';
        $metadata['type_of_contract'] = ['Agricultural Development Agreement'];
        $metadata['document_type']   = 'Company-State Contract';
        $metadata['open_contracting_id'] = 'ocds-591adf-TEST0001';

        $contract = new Contract();
        $contract->fill(array_merge([
            'metadata'        => $metadata,
            'metadata_trans'  => [],
            'file'            => 'smoke-test.pdf',
            'filehash'        => sha1('smoke-test'),
            'user_id'         => $this->admin()->id,
            'metadata_status' => Contract::STATUS_DRAFT,
            'text_status'     => Contract::STATUS_DRAFT,
        ], $overrides));
        $contract->created_datetime      = now();
        $contract->last_updated_datetime = now();
        $contract->save();

        return $contract->fresh();
    }

    public function test_contract_is_stored_and_listed(): void
    {
        $contract = $this->makeContract();

        $this->assertDatabaseHas('contracts', ['id' => $contract->id]);
        $this->assertSame('Smoke Test Contract', $contract->metadata->contract_name);

        $this->actingAs($this->admin())->get('/contract')
            ->assertOk()
            ->assertSee('Smoke Test Contract');
    }

    public function test_contract_show_and_edit_pages_render(): void
    {
        $contract = $this->makeContract();

        $this->actingAs($this->admin())->get('/contract/'.$contract->id)->assertOk();
        $this->actingAs($this->admin())->get('/contract/'.$contract->id.'/edit')->assertOk();
    }

    public function test_contract_metadata_json_endpoint(): void
    {
        $contract = $this->makeContract();

        $this->actingAs($this->admin())->get('/contract/'.$contract->id.'/metadata')
            ->assertOk();
    }

    public function test_contract_list_can_be_filtered(): void
    {
        $this->makeContract();

        $this->actingAs($this->admin())
            ->get('/contract?country=MN&status=draft&q=Smoke')
            ->assertOk();
    }
}

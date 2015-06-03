<?php use App\Nrgi\Services\Contract\ContractService;
use Tests\NrgiTestCase;

use \Mockery as m;

class ContractServiceTest extends NrgiTestCase
{
    protected $contractRepository;
    protected $auth;
    protected $storage;
    protected $uploadedFile;
    protected $contractService;
    protected $filesystem;

    public function setup()
    {
        parent::setUp();
        $this->contractRepository = m::mock('App\Nrgi\Repositories\Contract\ContractRepositoryInterface');
        $this->auth               = m::mock('Illuminate\Auth\Guard');
        $this->storage            = m::mock('Illuminate\Contracts\Filesystem\Factory');
        $this->filesystem         = m::mock('Illuminate\Filesystem\Filesystem');
        $this->uploadedFile       = m::mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        $this->contractService    = new ContractService(
            $this->contractRepository,
            $this->auth,
            $this->storage,
            $this->filesystem
        );

        $this->formData = [
            'project_title'  => '',
            'language'       => '',
            'country'        => '',
            'resource'       => '',
            'signature_date' => '',
            'signature_year' => '',
            'type_of_mining' => '',
            'contract_term'  => '',
        ];
    }

    public function testItShouldReturnContractCollection()
    {
        $collection = 'Illuminate\Database\Eloquent\Collection';
        $this->contractRepository->shouldReceive('getAll')->once()->andReturn(m::mock($collection));
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->contractService->getAll());
    }

    public function testItShouldGetContractModel()
    {
        $contractModel = 'App\Nrgi\Entities\Contract\Contract';
        $this->contractRepository->shouldReceive('findContract')->once()->with(1)->andReturn(m::mock($contractModel));
        $this->assertInstanceOf($contractModel, $this->contractService->find(1));
    }

    public function testItShouldSaveContract()
    {
        $user = m::mock('App\Nrgi\Entities\User\User');
        $this->auth->shouldReceive('user')->once()->andReturn($user);
        $user->shouldReceive('getAttribute')->once()->with('id')->andReturn(1);
        $this->uploadedFile->shouldReceive('isValid')->once()->andReturn(true);
        $this->uploadedFile->shouldReceive('getClientOriginalName')->once()->andReturn('file');
        $this->uploadedFile->shouldReceive('getClientOriginalExtension')->once()->andReturn('pdf');
        $this->storage->shouldReceive('disk->put')->once()->andReturn(true);
        $this->storage->shouldReceive('disk->getDriver->getAdapter->getClient->getObjectUrl')
                      ->once()
                      ->andReturn('url');
        $this->filesystem->shouldReceive('get')->once()->with($this->uploadedFile)->andReturn('file');
        $contract = 'App\Nrgi\Entities\Contract\Contract';
        $this->contractRepository->shouldReceive('save')->once()->andReturn(
            m::mock($contract)
        );

        $this->formData['file'] = $this->uploadedFile;
        $this->assertInstanceOf($contract, $this->contractService->saveContract($this->formData));
    }

    public function testItShouldNotSaveContractWhenInvalidFile()
    {
        $this->uploadedFile->shouldReceive('isValid')->once()->andReturn(false);
        $this->formData['file'] = $this->uploadedFile;
        $this->assertFalse($this->contractService->saveContract($this->formData));
    }

    public function testItShouldNotSaveContractWhenFileNotUploadedOnS3()
    {
        $user = m::mock('App\Nrgi\Entities\User\User');
        $this->uploadedFile->shouldReceive('isValid')->once()->andReturn(true);
        $this->uploadedFile->shouldReceive('getClientOriginalName')->once()->andReturn('file');
        $this->uploadedFile->shouldReceive('getClientOriginalExtension')->once()->andReturn('pdf');
        $this->storage->shouldReceive('disk->put')->once()->andReturn(false);
        $this->filesystem->shouldReceive('get')->once()->with($this->uploadedFile)->andReturn('file');
        $this->formData['file'] = $this->uploadedFile;
        $this->assertFalse($this->contractService->saveContract($this->formData));
    }

    function testItShouldDeleteContract()
    {
        $contract = m::mock('App\Nrgi\Entities\Contract\Contract');
        $this->contractRepository->shouldReceive('findContract')->once()->with(1)->andReturn($contract);
        $contract->shouldReceive('getAttribute')->once()->with('id')->andReturn(1);
        $contract->shouldReceive('getAttribute')->once()->with('filehash')->andReturn('filehash');
        $this->storage->shouldReceive('disk->exists')->once()->with('filehash')->andReturn(true);
        $this->contractRepository->shouldReceive('delete')->once()->with(1)->andReturn(true);
        $this->storage->shouldReceive('disk->delete')->once()->with('filehash')->andReturn(true);
        $this->assertTrue($this->contractService->deleteContract(1));
    }

    function testItShouldNotDeleteContractWhenCantRemoveFromDB()
    {
        $contract = m::mock('App\Nrgi\Entities\Contract\Contract');
        $this->contractRepository->shouldReceive('findContract')->once()->with(1)->andReturn($contract);
        $contract->shouldReceive('getAttribute')->once()->with('id')->andReturn(1);
        $this->contractRepository->shouldReceive('delete')->once()->with(1)->andReturn(false);
        $this->assertFalse($this->contractService->deleteContract(1));
    }

    function testItShouldUpdateContract()
    {
        $contract = m::mock('App\Nrgi\Entities\Contract\Contract');
        $this->contractRepository->shouldReceive('findContract')->once()->with('1')->andReturn($contract);
        $contract->shouldReceive('save')->once()->andReturn(true);
        $contract->shouldReceive('setAttribute')->once()->with('metadata', $this->formData)->andReturn([]);
        $this->assertTrue($this->contractService->updateContract(1, $this->formData));
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}

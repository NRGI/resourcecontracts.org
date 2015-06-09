<?php namespace Tests\App\Nrgi\Repositories\Contract;

use App\Nrgi\Repositories\Contract\ContractRepository;
use \Mockery as m;
use Tests\NrgiTestCase;

class ContractRepositoryTest extends NrgiTestCase
{
    protected $contractRepository;
    protected $contract;

    public function setUp()
    {
        parent::setUp();
        $this->contract           = m::mock('App\Nrgi\Entities\Contract\Contract');
        $this->contractRepository = new ContractRepository($this->contract);
    }

    public function testItShouldSaveContract()
    {
        $this->contract->shouldReceive('create')->once()->with(['contractDetails'])->andReturnSelf();
        $this->assertInstanceOf('App\Nrgi\Entities\Contract\Contract', $this->contractRepository->save(['contractDetails']));
    }

    public function testItShouldReturnContractModel()
    {
        $this->contract->shouldReceive('findOrFail')->once()->andReturnself();
        $this->assertInstanceOf('App\Nrgi\Entities\Contract\Contract', $this->contractRepository->findContract([]));
    }

    public function testItShouldReturnTrueIfContractDeleted()
    {
        $this->contract->shouldReceive('destroy')->once()->with($this->contract)->andReturn(true);
        $this->assertTrue($this->contractRepository->delete($this->contract));
    }

    public function testItShouldReturnContractCollection()
    {
        $this->contract->shouldReceive('orderBy->get')->once()->andReturn(m::mock('Illuminate\Database\Eloquent\Collection'));
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->contractRepository->getAll());
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}

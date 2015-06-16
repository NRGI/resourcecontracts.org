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
        $this->contract = m::mock('App\Nrgi\Entities\Contract\Contract');
        $this->db       = m::mock('Illuminate\Database\DatabaseManager');
        $this->contractRepository = new ContractRepository($this->contract, $this->db);
    }

    public function testItShouldSaveContract()
    {
        $this->contract->shouldReceive('create')->once()->with(['contractDetails'])->andReturnSelf();
        $this->assertInstanceOf(
            'App\Nrgi\Entities\Contract\Contract',
            $this->contractRepository->save(['contractDetails'])
        );
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
        $this->contract->shouldReceive('select->orderBy->get')->once()->with()->andReturn(
            m::mock('Illuminate\Database\Eloquent\Collection')
        );
        $this->assertInstanceOf(
            'Illuminate\Database\Eloquent\Collection',
            $this->contractRepository->getAll(['year' => '', 'country' => '', 'resource' => ''])
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}

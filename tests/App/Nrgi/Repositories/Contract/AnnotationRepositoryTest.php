<?php namespace Tests\App\Nrgi\Repositories\Contract\Annotation;

use Nrgi\Repositories\Contract\AnnotationRepository;
use \Mockery as m;
use Tests\NrgiTestCase;

class AnnotationRepositoryTest extends NrgiTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->annotation = m::mock('Nrgi\Entities\Contract\Annotation');
        $this->annotationRepo = new AnnotationRepository($this->annotation);
    }

    public function testItShouldGetAnAnnotationById()
    {
        $this->annotation->shouldReceive('findOrFail')->once()->with(1)->andReturnSelf();
        $this->assertInstanceOf('Nrgi\Entities\Contract\Annotation',
            $this->annotationRepo->getById(1));
    }

    public function testItShouldGetAnnotationList()
    {
        $this->annotation->shouldReceive('where->where->get')->once()->andReturn(m::mock('Illuminate\Database\Eloquent\Collection'));
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection',
            $this->annotationRepo->search(['contract'=>1,'document_page_no'=>1]));
    }

    public function testItShouldReturnInstanceOfAnnotationModel()
    {
        $this->annotation->shouldReceive('findOrNew')->once()->with(1)->andReturnSelf();
        $this->assertInstanceOf('Nrgi\Entities\Contract\Annotation', $this->annotationRepo->findOrCreate(1));
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}
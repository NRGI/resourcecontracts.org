<?php namespace Tests\App\Nrgi\Repositories\User;

use Tests\NrgiTestCase;

class UserRepositoryTest extends NrgiTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testShippableIntegration()
    {
        $this->assertTrue(true);
    }

    public function testShippableIntegrationFalseTest()
    {
        $this->assertFalse(false);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
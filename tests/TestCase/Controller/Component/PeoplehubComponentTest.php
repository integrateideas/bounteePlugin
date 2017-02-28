<?php
namespace Integrateideas\Peoplehub\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use Integrateideas\Peoplehub\Controller\Component\PeoplehubComponent;

/**
 * Integrateideas\Peoplehub\Controller\Component\PeoplehubComponent Test Case
 */
class PeoplehubComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Integrateideas\Peoplehub\Controller\Component\PeoplehubComponent
     */
    public $Peoplehub;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->Peoplehub = new PeoplehubComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Peoplehub);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

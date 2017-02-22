<?php
namespace Integrateideas\BounteePlugin\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use Integrateideas\BounteePlugin\Controller\Component\BounteeComponent;

/**
 * Integrateideas\BounteePlugin\Controller\Component\BounteeComponent Test Case
 */
class BounteeComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Integrateideas\BounteePlugin\Controller\Component\BounteeComponent
     */
    public $Bountee;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->Bountee = new BounteeComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Bountee);

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

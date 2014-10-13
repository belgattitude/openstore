<?php
namespace Openstore\Model;

use Openstore\Model\Customer;
use ModulesTests\ServiceManagerGrabber;
use Zend\ServiceManager\ServiceManager;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-13 at 19:23:29.
 */
class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Customer
     */
    protected $category;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;    
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $serviceManagerGrabber   = new ServiceManagerGrabber();
        $this->serviceManager = $serviceManagerGrabber->getServiceManager();        
        
        $this->category = $this->serviceManager->get('Model\Customer');
        
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }


    public function testGetDiscounts()
    {
        //$browser = $this->category->getBrowser();
        //$this->assertInstanceOf('Openstore\Model\Browser\CustomerBrowser', $browser);
        //$this->assertInstanceOf('Openstore\Core\Model\Browser\AbstractBrowser', $browser);
    }

}
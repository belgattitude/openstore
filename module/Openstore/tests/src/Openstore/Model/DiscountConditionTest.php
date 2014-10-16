<?php
namespace Openstore\Model;

use Openstore\Model\DiscountCondition;
use ModulesTests\ServiceManagerGrabber;
use Zend\ServiceManager\ServiceManager;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-13 at 19:23:29.
 */
class DiscountConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Openstore\Model\DiscountCondition
     */
    protected $dc;

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
        $this->dc = $this->serviceManager->get('Model\DiscountCondition');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }


    public function testGetDiscountStore()
    {
        $store = $this->dc->getDiscountStore();
        
        $source = $store->getSource();
        $select = $store->getSource()->getSelect();
        $select->where(array('dc.customer_id' => 3521));
        $select->where->nest
                        ->equalTo('pl.reference', 'FR')
                        ->or
                        ->isNull('pl.reference')
                       ->unnest;
        $select->order(array('pl.reference desc'));
        
        $data = $store->getData();
        $this->assertInstanceOf('Soluble\FlexStore\ResultSet\ResultSet', $data);
       // var_dump($data->toArray());
    }

}

<?php

namespace OpenstoreTest\Model;

use Openstore\Model\Order;
//use PHPUnit_Framework_TestCase;

use Soluble\Normalist\SyntheticTable;
use Soluble\Normalist\Exception as NormalistException;


//use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

//class OrderTest extends PHPUnit_Framework_TestCase
class OrderTest extends AbstractConsoleControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            //include '/var/www/zf2-tutorial/config/application.config.php'
			include dirname(__FILE__) . '/../../../../../config/application.config.php'	
        );
        parent::setUp();
    }
	
	
	/**
	 * @covers Openstore\Model\order::create
	 */	
	public function testCreateOrder()
	{
		$sm = $this->getApplication()->getServiceManager();
		$table = new SyntheticTable($sm->get('Zend\Db\Adapter\Adapter'));
		$orderModel = $sm->get('Model\Order');
		
		// create a standard web order
		$data = $this->getOrderData();
		$order = $orderModel->create($data);
		
		var_dump($order['updated_at']);
		die();
		$this->assertEquals($order['pricelist_id'], $data['pricelist_id']);
		$this->assertEquals($order['customer_id'], $data['customer_id']);
		$this->assertEquals($order['updated_at'], $data['customer_id']);
		
		$return = $order->delete();
		$this->assertEquals($return, true);
	}
	
	/**
	 * @covers Openstore\Model\order::create
	 */
	public function testAddOrderLine() 
	{
		$sm = $this->getApplication()->getServiceManager();
		$table = new SyntheticTable($sm->get('Zend\Db\Adapter\Adapter'));
		$orderModel = $sm->get('Model\Order');
		// create a standard web order
		
		$data = $this->getOrderData();
		$order = $orderModel->create($data);
		
		//$order->addOrderLine();
		
	}
	
	/**
	 * 
	 * @return \ArrayObject
	 */
	protected function getOrderData() {
		$sm = $this->getApplication()->getServiceManager();
		$table = new SyntheticTable($sm->get('Zend\Db\Adapter\Adapter'));
		$shopcart_order_type = $table->findOneBy('order_type', array('reference' => 'SHOPCART'));
		$data = new \ArrayObject(array(
			'customer_id' => 3521,
			'pricelist_id' => '1',
			'type_id' =>  $shopcart_order_type['type_id']
		));
		return $data;
	}
}
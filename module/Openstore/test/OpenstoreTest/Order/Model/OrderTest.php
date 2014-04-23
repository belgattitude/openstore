<?php

namespace OpenstoreTest\Order\Model;

use Openstore\Order\Model;
use Openstore\Entity;
use Doctrine\ORM\EntityManager;

//use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Openstore\Stdlib\Hydrator\DoctrineEntity;


//use PHPUnit_Framework_TestCase;

//use Soluble\Normalist\SyntheticTable;
//use Soluble\Normalist\Exception as NormalistException;


//use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

//class OrderTest extends PHPUnit_Framework_TestCase
class OrderTest extends AbstractConsoleControllerTestCase
{
    public function setUp()
    {
		
        $this->setApplicationConfig(
            //include '/var/www/zf2-tutorial/config/application.config.php'
			include dirname(__FILE__) . '/../../../../../../config/application.config.php'	
        );
		
        parent::setUp();
    }
	
	
	/**
	 * @return EntityManager
	 */
	protected function getEntityManager()
	{
		
		return $this->getApplication()->getServiceManager()->get('Doctrine\ORM\EntityManager');		
	}
	public function testDoctrine()
	{
		$em = $this->getEntityManager();
		$hydrator = new DoctrineEntity($em);
		
		$order = new Entity\SaleOrder();
		
		$types		= $em->getRepository('Openstore\Entity\SaleOrderType');
		$statuses	= $em->getRepository('Openstore\Entity\SaleOrderStatus');
		$pricelists = $em->getRepository('Openstore\Entity\Pricelist');
		
		$data = array(
			'customer_id'		=> 3521,
			'pricelist_id'		=> 1,
			'customer_reference'=> 'PHPUNIT-' . date('Y-m-d H:i:s'),
			'customer_comment'	=> 'Comment PHPUNIT-' . date('Y-m-d H:i:s'),
			'type_id'			=> $types->findOneBy(array('reference' => 'SHOPCART')),
			'status_id'			=> $statuses->findOneBy(array('flag_default' => 1)),
			'pricelist_id'		=> $pricelists->findOneBy(array('reference' => 'FR')),
			'user_id'			=> null,
		);

		
		$order = $hydrator->hydrate($data, $order);
		var_dump($order->getCustomerId()->getName());
		$em->persist($order);
		
		$order_id = $order->getOrderId();
		
		$line_status	= $em->getRepository('Openstore\Entity\SaleOrderLineStatus');
		
		$line = array(
			'order_id' => $order,
			'product_id' => 1,
			'status_id' => $line_status->findOneBy(array('flag_default' => 1)),
			'quantity' => 2,
			'price' => 10,
			'discount_1' => 0,
			'discount_2' => 0,
			'discount_3' => 0,
			'discount_4' => 0,
			'customer_reference'=> 'PHPUNIT-' . date('Y-m-d H:i:s'),
			'customer_comment'	=> 'Comment PHPUNIT-' . date('Y-m-d H:i:s'),
		);
		
		$orderline = new Entity\SaleOrderLine();
		$orderline = $hydrator->hydrate($line, $orderline);
		$em->persist($orderline);
		$em->flush();
		
		$hydrator->addStrategy('type_id', new \Openstore\Stdlib\Hydrator\Strategy\NestedExtractor());
		$a = $hydrator->extract($order);
		
		var_dump($a);
		die();
		$type = $a['type_id'];
		var_dump($type->getTitle());
		var_dump($type->getTypeId());
		foreach($type->getTranslations() as $tr) {
			var_dump($tr->getTitle());
		};
		die();
		
		var_dump($a['type_id']->getTitle());
		var_dump($a['type_id']->getTranslations()->toArray());
		//var_dump($a['type_id']->);
		
		die();
		
		
		
		$hydrator = new DoctrineObject(
			$em,
			'Openstore\Entity\SaleOrder'
		);
		
		$data = (array) $this->getOrderData();

		$order = $hydrator->hydrate($data, $order);		
		var_dump($order);
		die();
		
		$em->persist($order);
		$em->flush();
		$order->flush();
		
		die('cool');
		$customer = $em->getRepository('Openstore\Entity\Customer')->find($customer_id);
		$order->setCustomer($customer);
		
		$em->persist($order);
		$em->flush();
//		var_dump($order->getArrayCopy());
		var_dump($order);
		die('hello');
		//$sm = $this->getApplication()->getServiceManager();
		//$sm->
	}
/*
	public function testCreateOrderThrowsInvalidCustomerException()
	{
		$this->setExpectedException('Openstore\Order\Model\Exception\InvalidCustomerException');
		$sm = $this->getApplication()->getServiceManager();
		$st = new SyntheticTable($sm->get('Zend\Db\Adapter\Adapter'));
		
		$orderModel = $sm->get('Model\Order');
		
		// create a standard web order
		$data = $this->getOrderData();
		$data['customer_id'] = 'XXXXX999';
		
		$order = $orderModel->create($data);
	}
	
	
	public function testCreateOrder()
	{
		$sm = $this->getApplication()->getServiceManager();
		
		$orderModel = $sm->get('Model\Order');
		
		// create a standard web order
		$data = $this->getOrderData();
		$order = $orderModel->create($data);
		
		$this->assertEquals($data['pricelist_id'], $order['pricelist_id']);
		$this->assertEquals($data['customer_id'], $order['customer_id']);
		$this->assertEquals($data['customer_reference'], $order['customer_reference']);

		// Testing creation dates and update date
		$order_updated_at = \DateTime::createFromFormat('Y-m-d H:i:s', $order['updated_at']);
		$order_created_at = \DateTime::createFromFormat('Y-m-d H:i:s', $order['created_at']);
		$now = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
		$this->assertLessThan(2, abs($order_updated_at->diff($now)));
		$this->assertLessThan(2, abs($order_created_at->diff($now)));
		$this->assertContains('PHPUNIT', $order['customer_reference']);
		$this->assertContains('Comment PHPUNIT', $order['customer_comment']);
		
		$return = $order->delete();
		$this->assertEquals($return, true);
	}
	
	public function testAddOrderLine() 
	{
		$sm = $this->getApplication()->getServiceManager();
		$st = new SyntheticTable($sm->get('Zend\Db\Adapter\Adapter'));
		$orderModel = $sm->get('Model\Order');
		// create a standard web order
		
		$data = $this->getOrderData();
		$order = $orderModel->create($data);
		
		$order_id = $order['order_id'];
		
		$line_data = $this->getOrderLineData();
		
		$line = $orderModel->addOrderLine($order_id, $line_data);
		$line_id = $line['line_id'];
		$this->assertEquals($order_id, $line['order_id']);
		$this->assertEquals($line_data['quantity'], $line['quantity']);
		$this->assertEquals($line_data['discount_1'], $line['discount_1']);
		$this->assertEquals($line_data['product_id'], $line['product_id']);
		$this->assertContains('PHPUNIT', $line['customer_reference']);
		$this->assertContains('Comment PHPUNIT', $line['customer_comment']);
		
		$this->assertEquals($order['pricelist_id'], $line->getParent('order')->pricelist_id);
		$this->assertEquals($order['customer_id'], $line->getParent('order')->customer_id);
		
		// Test for prices
		$service	  = $sm->get('Openstore\Service');
		$productModel = $service->getModel('Model\Product');			

		$pricelist_reference = $order->getParent('pricelist')->reference;
		$product = $productModel->getBrowser()->setSearchParams(
							[
								'id'		 => $line_data['product_id'],
								'language'	 => '',
								'pricelist'  => $pricelist_reference,
							])
							->getStore()->getData()->current();
		$this->assertEquals($product['price'] * $line['quantity'], $line['price']);
		

		if ($delete = false) {
			$return = $order->delete();
			$this->assertEquals($return, true);

			// Test cascade relationship
			$this->assertFalse($st->exists('order_line', $line_id));
		}
		
	}
*/	
	/**
	 * 
	 * @return \ArrayObjectdie('cool');
	 */
	protected function getOrderData() {
		$sm = $this->getApplication()->getServiceManager();
		//$st = new SyntheticTable($sm->get('Zend\Db\Adapter\Adapter'));
		//$shopcart_order_type = $st->findOneBy('order_type', array('reference' => 'SHOPCART'));
		
		$data = new \ArrayObject(array(
			'customer_id' => 3521,
			'pricelist_id' => 1,
			'customer_reference' => 'PHPUNIT-' . date('Y-m-d H:i:s'),
			'customer_comment' => 'Comment PHPUNIT-' . date('Y-m-d H:i:s'),
			//'type_id' =>  $shopcart_order_type['type_id']
		));
		return $data;
	}
	
	protected function getOrderLineData() {
		
		$line_data = new \ArrayObject(array(
			'product_id' => 11303, // SW201N
			'quantity' => 2.0,
			'discount_1' => 0,
			'customer_reference' => 'PHPUNIT-' . date('Y-m-d H:i:s'),
			'customer_comment' => 'Comment PHPUNIT-' . date('Y-m-d H:i:s'),
		));
		
		return $line_data;
	}
}
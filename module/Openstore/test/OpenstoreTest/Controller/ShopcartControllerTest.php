<?php

namespace OpenstoreTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use Zend\Stdlib\Parameters;
use Zend\Json\Json;
		
class ShopcartControllerControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
		
        $this->setApplicationConfig(
			include dirname(__FILE__) . '/../../../../../config/application.config.php'	
        );
		
        parent::setUp();
    }
	
	/*
    protected function mockLogin()
    {
        $userSessionModel = new UserSessionModel();
        $userSessionModel->setUserId(1);
        $userSessionModel->setName('Tester');
 
        $authService = $this->getMock('Zend\Authentication\AuthenticationService');
        $authService->expects($this->any())
                    ->method('getIdentity')
                    ->will($this->returnValue($userSessionModel));
 
        $authService->expects($this->any())
                    ->method('hasIdentity')
                    ->will($this->returnValue(true));
 
        $this->getApplicationServiceLocator()->setAllowOverride(true);
        $this->getApplicationServiceLocator()->setService('Zend\Authentication\AuthenticationService', $authService);
    }*/	
	
	public function testAddProductActionWithSuccess()
	{
		$product_id = '11303';
		$quantity = 2.0;
		
		$this->getRequest()
			->setMethod('POST')
			->setPost(new Parameters(array(
				'product_id' => $product_id,
				'quantity' => $quantity,
				'discount_1' => 0
				
				)))	
			->getHeaders()->addHeaders(array(
					'X_REQUESTED_WITH' => 'XMLHttpRequest',
			));
		
		$this->dispatch('/shopcart/addproduct');
		$this->assertResponseStatusCode(200);
		$this->assertModuleName('Openstore');
		$this->assertControllerName('Openstore\Controller\Shopcart');
		$this->assertControllerClass('ShopcartController');
		$this->assertMatchedRouteName('shopcart/actions');
		
		$this->assertResponseHeaderContains('Content-Type', 'application/json; charset=utf-8');
		$response = $this->getResponse()->getContent();
		$decoded = Json::decode($response, Json::TYPE_ARRAY);
		
		$this->assertTrue($decoded['success']);
		$this->assertEquals($product_id, $decoded['data']['product_id']);
		$this->assertEquals($quantity, $decoded['data']['quantity']);

	}	
	
	public function testAddProductActionWithProductError()
	{
		$product_id = '1130300111';
		$quantity = 2.0;
		
		$this->getRequest()
			->setMethod('POST')
			->setPost(new Parameters(array(
				'product_id' => $product_id,
				'quantity' => $quantity,
				'discount_1' => 0
				
				)))	
			->getHeaders()->addHeaders(array(
					'X_REQUESTED_WITH' => 'XMLHttpRequest',
			));
		
		$this->dispatch('/shopcart/addproduct');
		$this->assertResponseStatusCode(200);
		$this->assertModuleName('Openstore');
		$this->assertControllerName('Openstore\Controller\Shopcart');
		$this->assertControllerClass('ShopcartController');
		$this->assertMatchedRouteName('shopcart/actions');
		
		$this->assertResponseHeaderContains('Content-Type', 'application/json; charset=utf-8');
		$response = $this->getResponse()->getContent();
		$decoded = Json::decode($response, Json::TYPE_ARRAY);
		
		$this->assertFalse($decoded['success']);
		
		

	}	
	
}
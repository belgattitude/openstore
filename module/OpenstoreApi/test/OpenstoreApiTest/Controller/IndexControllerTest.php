<?php

namespace OpenstoreApiTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
		
        $this->setApplicationConfig(
            //include '/var/www/zf2-tutorial/config/application.config.php'
			include dirname(__FILE__) . '/../../../../../config/application.config.php'	
        );
		
        parent::setUp();
    }
	
	/*
	public function testIndexActionCanBeAccessed()
	{
		$this->dispatch('/');
		$this->assertResponseStatusCode(200);

		$this->assertModuleName('OpenstoreApi');
		$this->assertControllerName('OpenstoreApi\Controller\Index');
		$this->assertControllerClass('IndexController');
		//$this->assertMatchedRouteName('album');
	}
	 * 
	 */	
}
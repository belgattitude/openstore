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
	
	
}
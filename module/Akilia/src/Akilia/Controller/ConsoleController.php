<?php
namespace Akilia\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\Console\Request as ConsoleRequest;

use Akilia;

class ConsoleController extends AbstractActionController
{

    public function syncdbAction()
	{
		
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->synchronizeAll();	
    }
	
}

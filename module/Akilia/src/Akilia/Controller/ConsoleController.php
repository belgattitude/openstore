<?php
namespace Akilia\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\Console\Request as ConsoleRequest;

use Akilia;

class ConsoleController extends AbstractActionController
{

    public function syncdbAction()
	{
		$sl = $this->getServiceLocator();
		$configuration = $sl->get('Configuration');
		if (!is_array($configuration['akilia'])) {
			throw new Exception("Cannot find akilia configuration, please see you global config files");
		}
		if (!is_array($configuration['akilia']['synchronizer'])) {
			throw new Exception("Cannot find akilia synchronize configuration, please see you global config files");
		}

		
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->setConfiguration($configuration['akilia']['synchronizer']);
		$synchronizer->synchronizeAll();	
    }
	
}

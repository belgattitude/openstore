<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Openstore\Controller;


use Openstore\Entity;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

use Zend\Console\Request as ConsoleRequest;

use Nv\Akilia;

class ConsoleController extends AbstractActionController
{
	public function setupAction()
	{
		echo 'setup done';
	}

	/**
	 * recreate db and load data fixtures
	 * 
	 */
	public function updatedbAction()
	{
		
		$dir = realpath(__DIR__ . '/../../../../../');
		$vendor_dir = $dir . '/vendor';
		$php = "/usr/local/bin/php";
		
		$commands = array(
			"$php $vendor_dir/bin/doctrine-module orm:schema:update --force",
			"$php $vendor_dir/bin/doctrine-module data-fixture:import",
		);
		
		foreach($commands as $command) {
			echo "Executing $command\n";
			passthru($command);
		}
		
	}
	
	
	/**
	 * recreate db and load data fixtures
	 * 
	 */
	public function recreatedbAction()
	{
		$dir = realpath(__DIR__ . '/../../../../../');
		$vendor_dir = $dir . '/vendor';
		$php = "/usr/local/bin/php";
		
		$commands = array(
			"$php $vendor_dir/bin/doctrine-module orm:schema:drop --force",
			"$php $vendor_dir/bin/doctrine-module orm:schema:create",
			"$php $vendor_dir/bin/doctrine-module data-fixture:import",
		);
		
		foreach($commands as $command) {
			echo "Executing $command\n";
			passthru($command);
		}
		
	}
	
    public function akiliasyncdbAction()
	{
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->synchronizeAll();	

    }
	
}

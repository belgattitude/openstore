<?php

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
	
	
	public function clearcacheAction() {
		$serviceLocator = $this->getServiceLocator();
		if ($serviceLocator->has('Cache\SolubleDbMetadata')) {
			/** @var \Zend\Cache\Storage\StorageInterface */
			$cache = $serviceLocator->get('Cache\SolubleDbMetadata');
			$cache->flush();
		}		
		
		
	}
	
	public function clearmediacacheAction() {
		$serviceLocator = $this->getServiceLocator();
		if ($serviceLocator->has('Cache\SolubleMediaConverter')) {
			/** @var \Zend\Cache\Storage\StorageInterface */
			$cache = $serviceLocator->get('Cache\SolubleMediaConverter');
			$cache->flush();
			//$cache->clearByNamespace('Cache\SolubleMediaConverter');
		}		
		
		
	}	

	/**
	 * recreate db and load data fixtures
	 * 
	 */
	public function updatedbAction()
	{
		
		$dir = realpath(__DIR__ . '/../../../../../');
		$php = "/usr/local/bin/php";

		
		$commands = array(
			"$php $dir/public/index.php orm:schema-tool:update --force",
			"$php $dir/public/index.php data-fixture:import",
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
		$php = "/usr/local/bin/php";
		
		$commands = array(
			"$php $dir/public/index.php orm:schema-tool:drop --force",
			"$php $dir/public/index.php orm:schema-tool:create",
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
	public function buildallreloadAction()
	{
		$dir = realpath(__DIR__ . '/../../../../../');
		
		$php = "/usr/local/bin/php";
		

		$commands = array(
			"$php $dir/public/index.php orm:schema-tool:drop --force",
			"$php $dir/public/index.php orm:schema-tool:create",
			"$php $dir/public/index.php data-fixture:import",
		);
		
		foreach($commands as $command) {
			echo "Executing $command\n";
			passthru($command);
		}
	}
	
	public function relocategroupcategAction()
	{
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');		
		
		// Step 1: Adding categories
		
		$root_reference = 'ROOT';

		// If group categ does not exists
		$rootCategory = $em->getRepository('Openstore\Entity\ProductCategory')->findOneBy(array('reference' => $root_reference));
		if ($rootCategory === null) {
			$rootCategory = new \Openstore\Entity\ProductCategory();
			$rootCategory->setReference($root_reference);
			$rootCategory->setTitle('ROOT');
			$em->persist($rootCategory);
			$em->flush();
		}
		
		// Select all product groups
		$select = "
			select 
				pg.group_id,
				pg.reference,
				pg.title,
				pg.legacy_mapping,
				pc.category_id
			from product_group pg
			left outer join product_category pc on pc.legacy_mapping = pg.legacy_mapping			
		";
		
		$rows = $em->getConnection()->query($select)->fetchAll();
		
		foreach($rows as $row) {
			if ($row['category_id'] === null) {
				$pc = new \Openstore\Entity\ProductCategory;
			} else {
				$pc = $em->find('Openstore\Entity\ProductCategory', $row['category_id']);
			}
			$pc->setParent($rootCategory);
			$pc->setTitle($row['title']);
			$pc->setReference($row['reference']);
			$pc->setLegacyMapping($row['legacy_mapping']);			
			$em->persist($pc);
		}
		$em->flush();	
		
		// Step 2, putting products in group
		
		$update = "
			update product p
			inner join product_group pg on pg.group_id = p.group_id
			inner join product_category pc on pc.legacy_mapping = pg.legacy_mapping
			set p.category_id = pc.category_id
		";
		
		$result = $em->getConnection()->query($update);
		
	}	

	
	
}

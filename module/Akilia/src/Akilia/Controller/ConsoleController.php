<?php
namespace Akilia\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Console;
use Zend\Console\ColorInterface;
use Zend\Db\Adapter\Adapter;

use Akilia\Utils\Akilia1Products;
use Akilia;

class ConsoleController extends AbstractActionController
{

    public function syncdbAction()
	{
		$configuration = $this->getAkiliaConfiguration();
		if (!is_array($configuration['synchronizer'])) {
			throw new \Exception("Cannot find akilia synchronize configuration, please see you global config files");
		}

		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->setServiceLocator($this->getServiceLocator());
		$synchronizer->setConfiguration($configuration['synchronizer']);
		$synchronizer->synchronizeAll();	
    }
	
	
	public function listproductpicturesAction() {
		$sl = $this->getServiceLocator();
		$configuration = $this->getAkiliaConfiguration();
		
		$products = new Akilia1Products($configuration);
		$products->setServiceLocator($this->getServiceLocator());
		$products->setDbAdapter($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		$list = $products->getProductPictures();
		
		foreach($list as $infos) {
			echo str_pad($infos['product_id'], 10) . "\t" .  
				($infos['alternate_index'] === null ? "0" : $infos['alternate_index']) . "\t" . 
				($infos['product_active'] ? "        " : "ARCHIVED") . "\t" . 
				$infos['filename'] . "\n"; 
		}
	}
	
	public function archiveproductpicturesAction() {
		
		
		$configuration = $this->getAkiliaConfiguration();
		$products = new Akilia1Products($configuration);
		$products->setServiceLocator($this->getServiceLocator());
		$products->setDbAdapter($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		$list = $products->getProductPictures();

		$archive_picture_path = $configuration['archive_product_picture_path'];
		if (!is_dir($archive_picture_path)) {
			throw new \Exception("archive_product_picture_path '$archive_picture_path' is not a valid directory, correct your config");
		} elseif (!is_writable($archive_picture_path)) {
			throw new \Exception("archive_product_picture_path '$archive_picture_path' is not writable");
		}

		
		$archivable = array();
		foreach($list as $basename => $infos) {
			if (!$infos['product_active']) {
				$archivable[] = $infos['filename'];
			}
		}
		
		$console = Console::getInstance();
		
		if (count($archivable) == 0) {
			$console->writeLine("Nothing to do (no archivable product picture found)", ColorInterface::GREEN);
			exit(0);
		} else {
			foreach($archivable as $file) {
				echo "$file\n";
			}
			$count = count($archivable);
			$console->writeLine('---------------------------------------------------');
			$console->writeLine("Will move $count file(s) into new directory :");
			$console->writeLine(" - $archive_picture_path");
			$console->writeLine('---------------------------------------------------');
			$console->writeLine("Are you okay ? (CTRL-C to abort)");
			$console->readLine();
			try {
				foreach($archivable as $file) {
					$dest_file = $archive_picture_path . "/" . basename($file);
					$ret = copy($file, $dest_file);
					if (!$ret) {
						throw new \Exception("Cannot copy '$file' to '$dest_file'");
					}
					$ret = unlink($file);
					if (!$ret) {
						throw new \Exception("Cannot remove '$file'");
					}
					
				}
			} catch (\Exception $e) {
				$console->writeLine("Error: {$e->getMessage()}", ColorInterface::RED);
			}
			$console->writeLine("Success, $count files moved to $archive_picture_path", ColorInterface::GREEN);
			
		}

		
		
	}
	

	/**
	 * 
	 * @return array
	 * @throws Exception
	 */
	protected function getAkiliaConfiguration() {
		$sl = $this->getServiceLocator();
		$configuration = $sl->get('Configuration');
		if (!is_array($configuration['akilia'])) {
			throw new \Exception("Cannot find akilia configuration, please see you global config files");
		}
		return $configuration['akilia'];
		
	}
}

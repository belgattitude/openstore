<?php
namespace SolubleNormalist\Controller;
use Zend\Mvc\Controller\AbstractConsoleController;
use Soluble\Normalist\Driver\ZeroConfDriver;


class ConsoleController extends AbstractConsoleController
{
	public function generatemodelsAction()
	{
		$driver = $this->getDriver();
		$file = $driver->getModelsConfigFile();
		if (file_exists($file)) {
			unlink($file);
		}
		$driver->getMetadata();
		
	}
	
	/**
	 * @return ZeroConfDriver
	 */
	protected function getDriver()
	{
		$sl = $this->getServiceLocator();
		return $sl->get('SolubleNormalist\Driver');
	}
}

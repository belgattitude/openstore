<?php
namespace Openstore;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $locator
     * @return \Openstore\Service
     */
    public function createService(ServiceLocatorInterface $sl)
    {
		
        $configuration = $sl->get('Openstore\Configuration');
		$adapter	   = $sl->get('Zend\Db\Adapter\Adapter');
		
        $service = new Service($configuration, $adapter);
		
		$service->setServiceLocator($locator);
        return $service;
    }
}

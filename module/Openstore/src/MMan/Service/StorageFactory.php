<?php

namespace MMan\Service;

use MMan\Service\Storage;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Gaufrette\Adapter as GAdapter;

class StorageFactory implements FactoryInterface
{
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return \MMan\Service\Manager
	 */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $config = isset($config['mediamanager']) ? $config['mediamanager'] : array();
		if (empty($config)) {
			throw new \Exception("Cannot locate media manager configuration, please review your configuration.");
		}
		
		$adapterConfig = $config['adapter'];
		$adapterClass   = $adapterConfig['class'];
		$adapterOptions = $adapterConfig['options'];
		switch ($adapterClass) {
			case 'Gaufrette\Adapter\Local':
				$basePath = $adapterOptions['basePath'];
				$adapter = new GAdapter\Local($basePath);
				break;
			case 'Gaufrette\Adapter\SafeLocal':
				$basePath = $adapterOptions['basePath'];
				$adapter = new GAdapter\SafeLocal($basePath);
				break;
			default: 
				throw new \Exception("Cannot load mediamanager adapter '$adapterClass', adapter is not supported");
		}
		$manager = new Storage();
		$manager->setAdapter($adapter);
		$manager->setAdapterOptions($adapterOptions);
		$manager->setServiceLocator($serviceLocator);

        return $manager;
    }
}
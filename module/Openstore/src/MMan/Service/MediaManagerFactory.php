<?php

namespace MMan\Service;

use MMan\MediaManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

//use Gaufrette\Adapter as GAdapter;

class MediaManagerFactory implements FactoryInterface
{
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return \MMan\MediaManager
	 */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$storage = $serviceLocator->get('MMan\Storage');
		$mediaManager = new MediaManager();
		$mediaManager->setStorage($storage);
		$mediaManager->setDbAdapter($serviceLocator->get('Zend\Db\Adapter\Adapter'));
		$mediaManager->setSyntheticTable($serviceLocator->get('Soluble\Normalist\SyntheticTable'));
		
		return $mediaManager;
    }
}
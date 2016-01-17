<?php

namespace MMan\Service;

use MMan\MediaManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

//use Gaufrette\Adapter as GAdapter;

class MediaManagerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MediaManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $storage = $serviceLocator->get('MMan\Storage');
        $mediaManager = new MediaManager();
        $mediaManager->setStorage($storage);
        $tm = $serviceLocator->get('SolubleNormalist\TableManager');
        $mediaManager->setTableManager($tm);

        return $mediaManager;
    }
}

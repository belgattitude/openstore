<?php

namespace SolubleNormalist\Service;

use Soluble\Normalist\Synthetic\TableManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SolubleNormalist\Service\Exception;

class TableManagerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return TableManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $default_connection = 'default';

        $driver = $serviceLocator->get('SolubleNormalist\Driver');

        $tm = new TableManager($driver);
        return $tm;
    }
}

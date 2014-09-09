<?php

namespace License\Service;

use License\LicenseManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class LicenseManagerFactory implements FactoryInterface {

    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return LicenseManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        
        $config = $serviceLocator->get('Config');

        $config = isset($config['licenses']) ? $config['licenses'] : array();
        if (empty($config)) {
            throw new \Exception("Cannot locate licenses configuration, please review your configuration.");
        }

        $licenseManager = new LicenseManager($config);


        return $licenseManager;
    }

}

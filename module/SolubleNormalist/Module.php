<?php

namespace SolubleNormalist;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface {

    /**
     * {@inheritDoc}
     */
    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @inheritdoc
     */
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'SolubleNormalist\Driver' => 'SolubleNormalist\Service\NormalistDriverFactory',
                'SolubleNormalist\TableManager' => 'SolubleNormalist\Service\TableManagerFactory',
            )
        );
    }

    public function getConsoleUsage(AdapterInterface $console) {
        return array(
            'normalist generate-models' => 'Regenerate normalist models',
        );
    }

}

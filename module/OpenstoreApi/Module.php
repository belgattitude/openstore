<?php

namespace OpenstoreApi;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{

	public function init(ModuleManager $moduleManager) {

		
	}

	public function onBootstrap(MvcEvent $e) {

        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
	}

	
	

	public function getConfig() {
		
		$config = array_merge(
				include __DIR__ . '/config/module.config.php', 
				include __DIR__ . '/config/routes.config.php'
		);
		
		return $config;
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


}

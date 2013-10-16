<?php

namespace OpenstoreApi;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

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
	
	public function getServiceConfig() {
		return array(
			'initializers' => array(
				'db' => function($service, $sm) {
							if ($service instanceof AdapterAwareInterface) {
								$service->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
							}
					},
				'sm' => function($service, $sm) {
						if ($service instanceof ServiceLocatorAwareInterface) {
							$service->setServiceLocator($sm);
						}
				}
			),			
			'aliases' => array(
			
			),
			'invokables' => array(
				'Api\MediaService' => 'OpenstoreApi\Api\MediaService',
			)
		);
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

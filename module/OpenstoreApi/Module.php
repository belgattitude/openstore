<?php

namespace OpenstoreApi;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Soluble\FlexStore\FlexStoreInterface;
use Soluble\FlexStore\Writer\Zend\Json as JsonWriter;
use Soluble\FlexStore\Writer\CSV as CSVWriter;
use Soluble\FlexStore\Writer\SimpleXmlWriter;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{

	public function init(ModuleManager $moduleManager) {
		
	}

	public function onBootstrap(MvcEvent $e) {

		/** @var \Zend\ModuleManager\ModuleManager $moduleManager */
		$moduleManager = $e->getApplication()->getServiceManager()->get('modulemanager');
		//$moduleManager->
		/** @var \Zend\EventManager\SharedEventManager $sharedEvents */
		
    
		
		$sharedEvents = $moduleManager->getEventManager()->getSharedManager();		
		$sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
		$sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
		
		//$eventManager = $moduleManager->getEventManager();
		/*
		$eventManager        = $e->getApplication()->getEventManager();		
		$eventManager->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
		$eventManager->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
		*/
		
		/*
		
		 * 
		 */

	}

	/**
	 * @param MvcEvent $e
	 * @return null|\Zend\Http\PhpEnvironment\Response
	 */
	public function postProcess(MvcEvent $e) {
		
		$routeMatch = $e->getRouteMatch();
		if ($routeMatch) {
			$format = $routeMatch->getParam('format', false);

			if ($e->getResult() instanceof \Zend\View\Model\ViewModel) {
				if (is_array($e->getResult()->getVariables())) {
					$vars = $e->getResult()->getVariables();
				} else {
					$vars = null;
				}
			} else {
				$vars = $e->getResult();
			}


			switch($format) {
				case 'json' :
					if ($vars instanceof FlexStoreInterface) {
						$jsonWriter = new JsonWriter($vars->getSource());
						$jsonWriter->send();
						die();
					} else {
						throw new \Exception('Writer does not support FlexStoreInterface');
					}
					break;
				case 'xml' :

					if ($vars instanceof FlexStoreInterface) {
						$xmlWriter = new SimpleXmlWriter($vars->getSource());
						$xmlWriter->send();
						die();
					} else {
						throw new \Exception('Writer does not support FlexStoreInterface');
					}

					break;
				case 'csv' :
					if ($vars instanceof FlexStoreInterface) {
						$options = array(
							'charset' => 'ISO-8859-1',
							'field_separator' => CSVWriter::SEPARATOR_TAB,
							'line_separator' => CSVWriter::SEPARATOR_NEWLINE_UNIX,
							
						);
						$csvWriter = new CSVWriter($vars->getSource());
						$csvWriter->setOptions($options);
						$csvWriter->send();
						die();
					} else {
						throw new \Exception('Writer does not support FlexStoreInterface');
					}
					

				default:
					throw new \Exception('error format not supported');

			}
		}
	}

	/**
	 * @param MvcEvent $e
	 * @return null|\Zend\Http\PhpEnvironment\Response
	 */
	public function errorProcess(MvcEvent $e) {
		$routeMatch = $e->getRouteMatch();
		if (php_sapi_name() != 'cli' && $routeMatch !== null) {
			
			$format = $routeMatch->getParam('format', false);		
			$eventParams = $e->getParams();

			/** @var array $configuration */
			$configuration = $e->getApplication()->getConfig();

			$body = array(
				'message'	=> 'Something went wrong',
				'success'	=> 0,
				'error' => array(
					'type' => $eventParams['error'],
				)
			);

			if (isset($eventParams['exception'])) {

				/** @var \Exception $exception */
				$exception = $eventParams['exception'];

				if ($configuration['errors']['show_exceptions']['message']) {
					$body['error']['exception_message'] = $exception->getMessage();
				}
				if ($configuration['errors']['show_exceptions']['trace']) {
					$body['error']['exception_trace'] = $exception->getTrace();
				}
			}


			switch ($format) {
				case 'json' :


					$e->getResponse()->setContent(json_encode($body));
					$e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/json', true);

					break;

				case 'xml' :
					$e->getResponse()->setContent("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><success>0</success></response>");
					$e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/xml', true);

					break;

				default:


			}

			if (
					$eventParams['error'] === \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND ||
					$eventParams['error'] === \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH
			) {
				$e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_501);
			} else {
				$e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_500);
			}

			$e->stopPropagation();
			return $e->getResponse();
		}	
	}

	public function getConfig() {

		$config = array_merge(
				include __DIR__ . '/config/module.config.php', include __DIR__ . '/config/routes.config.php'
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
				'Api\ProductMediaService' => 'OpenstoreApi\Api\ProductMediaService',
				'Api\ProductCatalogService' => 'OpenstoreApi\Api\ProductCatalogService',
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

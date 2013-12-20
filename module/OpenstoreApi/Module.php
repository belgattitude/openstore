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

use OpenstoreApi\Authorize\Exception\AuthorizationException;

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
		//$sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
		$sharedEvents->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
		//$sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
		$sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
		
		//$eventManager = $moduleManager->getEventManager();
		
		//$eventManager        = $e->getApplication()->getEventManager();		
		
		//$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'postProcess'), -100);
		//$eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'errorProcess'), 999);
		/*
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
						$jsonWriter->setDebug($debug = false);
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
		
		//var_dump($e->getApplication()->get);die();
		if (php_sapi_name() != 'cli' && $routeMatch !== null
				&& $routeMatch->getMatchedRouteName() == 'api/restful') {
			
		
			
			$format = $routeMatch->getParam('format', false);		
			$eventParams = $e->getParams();

			/** @var array $configuration */
			$configuration = $e->getApplication()->getConfig();
			$error_message = "Something went wrong";
			
			$error_type = $eventParams['error'];

			$body = array(
				'message'	=> $error_message,
				'success'	=> 0,
				'error' => array(
					'type' => $error_type,
				)
			);
			
			
			if (isset($eventParams['exception'])) {
				
				$reason_phrase = "Error: " . $eventParams['exception']->getMessage();
				
				/** @var \Exception $exception */
				$exception = $eventParams['exception'];
				
				if ($exception instanceof AuthorizationException) {
					$error_type = "authorization-exception";
					$body['error']['type'] = $error_type;
					$body['message'] = "Authorization error, " . $exception->getMessage();
				}
				
				if ($configuration['errors']['show_exceptions']['message']) {
					$body['error']['exception_message'] = $exception->getMessage();
				}
				if ($configuration['errors']['show_exceptions']['trace']) {
					$body['error']['exception_trace'] = $exception->getTrace();
				}
			} else {
				$reason_phrase = "Error, something went wrong.";
			}

			

			switch ($format) {
				case 'json' :

					$e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/json', true);
					$e->getResponse()->setContent(json_encode($body));
					break;

				case 'xml' :
					
					$e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/xml', true);
					$exception_message = $body['error']['exception_message'];
					$error_type = $body['error']['type'];
					$message = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
					$message .= "<response>\n\t<success>0</success>\n\t<message>$reason_phrase</message>";
					$message .=	"<error>\n\t<type>$error_type</type>";
					$message .= "\t<exception_message>$exception_message</exception_message></error>\n</response>";
					$e->getResponse()->setContent($message);
					break;

				default:

					$e->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'text/html', true);
					$content = $reason_phrase;
					$e->getResponse()->setContent($content);
					break;


			}

			if (
					$eventParams['error'] === \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND ||
					$eventParams['error'] === \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH
			) {
				$e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_501);
			} else {
				$e->getResponse()->setStatusCode(\Zend\Http\PhpEnvironment\Response::STATUS_CODE_500);
				if ($reason_phrase != '') {
					$e->getResponse()->setReasonPhrase($reason_phrase);						
				}
					
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
				
				'Authorize\ApiKeyAccess' => 'OpenstoreApi\Authorize\ApiKeyAccess',
				
				'Api\ProductMediaService' => 'OpenstoreApi\Api\ProductMediaService',
				'Api\ProductCatalogService' => 'OpenstoreApi\Api\ProductCatalogService',
				'Api\ProductBrandService' => 'OpenstoreApi\Api\ProductBrandService',
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

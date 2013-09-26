<?php

namespace Openstore;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Openstore\Configuration;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
//use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Session\SessionManager;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Config\StandardConfig;
use Zend\Session\Container;
use HTMLPurifier;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface
{

	public function init(ModuleManager $moduleManager) {

		
		
		
		/*
		  $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
		  $sharedEvents->attach('ZfcUser', 'dispatch', function(MvcEvent $e) {
		  $controller = $e->getTarget();
		  var_dump(get_class_methods($controller));
		  echo '<pre>';
		  $serviceManager = $e->getApplication()->getServiceManager();
		  var_dump($e->getViewModel()->getOptions());
		  $templatePathResolver = $serviceManager->get('Zend\View\Resolver\TemplatePathStack');

		  $t = $controller->getServiceLocator()->get('Zend\View\Resolver\TemplateMapResolver');

		  var_dump($t);

		  //$path =  $templatePathResolver->getPaths()->pop();
		  //$templatePathResolver->getPaths()->unshift($path);
		  $paths = $templatePathResolver->getPaths();
		  foreach($paths as $idx => $path) {
		  var_dump($path);

		  }
		  //$templatePathResolver->getPaths()->push(__DIR__ . '/view'); // here is your skin name

		  var_dump($templatePathResolver);
		  echo '</pre>';
		  var_dump(__DIR__);
		  //$templatePathResolver->addPaths(array(__DIR__ . '/view/zfc-user')); // here is your skin name

		  }, 200);
		 */
	}

	public function onBootstrap(MvcEvent $e) {

        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->bootstrapSession($e);
		$this->configureZfcUser($e);
		
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onPreDispatch'), 100);
		$eventManager->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'), 100);
		
		//$translator = $e->getApplication()->getServiceManager()->get('translator');
		//$translator->setLocale('en_US');
		//$translator->setFallbackLocale('fr_FR');    		
	}

	protected function configureZfcUser(MvcEvent $e) {

		$serviceManager	  = $e->getApplication()->getServiceManager();		
		$zfcServiceEvents = $serviceManager->get('ZfcUser\Authentication\Adapter\AdapterChain')->getEventManager();
		
		$zfcServiceEvents->attach(
			'authenticate',
			function ($e) use ($serviceManager) {
				$user = $e->getParams();
				if ($user['code'] == 1) {
					// authentication successfull
					$user_id = $user['identity'];
					// Save in session
					$userContainer = new Container('Openstore\UserContext');
					$userContainer['user_id']		 = $user_id;
					$userContainer['is_logged']		 = true;
					$userContainer['is_initialized'] = false;
					
				} else {
					// reset sesssion
					$serviceManager->get('Zend\Session\SessionManager')->getStorage()->clear('Openstore\UserContext');
				}
				return true;
			}
		);		
			
		$zfcServiceEvents->attach(
			'logout',
			function ($e) use ($serviceManager) {
				$serviceManager->get('Zend\Session\SessionManager')->getStorage()->clear('Openstore\UserContext');
			}
		);					
	}
	
	protected function bootstrapSession(MvcEvent $e) {
		$session = $e->getApplication()
				->getServiceManager()
				->get('Zend\Session\SessionManager');
		$session->start();

		$container = new Container('initialized');
		if (!isset($container->init)) {
			$session->regenerateId(true);
			$container->init = 1;
		}
	}


	public function onPreDispatch(MvcEvent $e) {
		$app = $e->getTarget();

		// TODO
		$language = $e->getRouteMatch()->getParam('ui_language');
		$supported_langs = array(
			'fr' => 'fr_FR',
			'en' => 'en_US',
			'nl' => 'nl_NL',
			'de' => 'de_DE'
		);
		if ($language != '' && array_key_exists($language, $supported_langs)) {
			$serviceManager = $app->getServiceManager();
			$serviceManager->get('translator')->setLocale($supported_langs[$language]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getServiceConfig() {
		return array(
			'aliases' => array(
			//'ZendDeveloperTools\ReportInterface' => 'ZendDeveloperTools\Report',
			),
			'invokables' => array(
				'Model\Product' => 'Openstore\Model\Product',
				'Model\Category' => 'Openstore\Model\Category',
				'Model\Brand' => 'Openstore\Model\Brand',
				'Model\ProductSerie' => 'Openstore\Model\ProductSerie',
				'Model\User' => 'Openstore\Model\User',
				'Model\Pricelist' => 'Openstore\Model\Pricelist',
				'Model\Customer' => 'Openstore\Model\Customer',
			),
			'factories' => array(
				'Openstore\Configuration' => 'Openstore\ConfigurationFactory',
				'Openstore\Service' => 'Openstore\ServiceFactory',
				'Openstore\PriceManager' => 'Openstore\Catalog\PriceManagerFactory',
				'Openstore\StockManager' => 'Openstore\Catalog\StockManagerFactory',
				'Openstore\UserCapabilities' => 'Openstore\Permission\UserCapabilitiesFactory',
				'Openstore\UserContext' => function ($sm) {
					$userContainer = new Container('Openstore\UserContext');
					$userContext = new \Openstore\UserContext($userContainer);
					$userContext->setServiceLocator($sm);
					$userContext->initialize();
					return $userContext;
				},
				'Zend\Session\SessionManager' => function ($sm) {
					$config = $sm->get('config');
					if (isset($config['session'])) {
						
						$session = $config['session'];
						$sessionConfig = null;
						if (isset($session['config'])) {
							$class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
							$options = isset($session['config']['options']) ? $session['config']['options'] : array();
 
							$sessionConfig = new $class();
							$sessionConfig->setOptions($options);
							
						}

						$sessionStorage = null;
						if (isset($session['storage'])) {
							$class = $session['storage'];
							$sessionStorage = new $class();
							
						}

						$sessionSaveHandler = null;
						if (isset($session['save_handler'])) {
							
							// class should be fetched from service manager since it will require constructor arguments
							$sessionSaveHandler = $sm->get($session['save_handler']);
							
						}

						$sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

						if (isset($session['validator'])) {
							$chain = $sessionManager->getValidatorChain();
							foreach ($session['validator'] as $validator) {
								$validator = new $validator();
								$chain->attach('session.validate', array($validator, 'isValid'));
							}
						}
					} else {
						$sessionManager = new SessionManager();
					}
					Container::setDefaultManager($sessionManager);
					return $sessionManager;
				},
			)
		);
	}
	
	public function onFinish(MvcEvent $e) {


		$purify_method = 'htmlpurifier';
		//$purify_method = 'domdocument';
		$purify_method = '';
		switch ($purify_method) {
			case 'htmlpurifier' :
				$response = $e->getResponse();
				$content = $response->getBody();

				$config = \HTMLPurifier_Config::createDefault();
				$config->set('Cache.SerializerPath', dirname(__FILE__) . '/../../data/cache');
				$purifier = new \HTMLPurifier($config);
				$clean_html = $purifier->purify($content);
				if ($clean_html !== false) {
					$response->setContent($clean_html);
				}

				break;

			case 'domdocument' :

				$response = $e->getResponse();
				$content = $response->getBody();

				$dom = new \DOMDocument();
				$dom->preserveWhiteSpace = false;
				$dom->formatOutput = false;
				$dom->recover = false;
				$dom->strictErrorChecking = false;
				$dom->formatOutput = true;

				$dom->loadHTML($content, LIBXML_NOBLANKS);

				// do stuff here
				$clean_html = $dom->saveHTML();

				if ($clean_html !== false) {
					$response->setContent($clean_html);
				}

				break;
		}
	}
	

	public function getConfig() {

		$config = array_merge(
				include __DIR__ . '/config/module.config.php', include __DIR__ . '/config/routes.config.php', include __DIR__ . '/config/openstore.config.php'
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

	/**
	 * Returns an array or a string containing usage information for this module's Console commands.
	 * The method is called with active Zend\Console\Adapter\AdapterInterface that can be used to directly access
	 * Console and send output.
	 *
	 * If the result is a string it will be shown directly in the console window.
	 * If the result is an array, its contents will be formatted to console window width. The array must
	 * have the following format:
	 *
	 *     return array(
	 *                'Usage information line that should be shown as-is',
	 *                'Another line of usage info',
	 *
	 *                '--parameter'        =>   'A short description of that parameter',
	 *                '-another-parameter' =>   'A short description of another parameter',
	 *                ...
	 *            )
	 *
	 * @param AdapterInterface $console
	 * @return array|string|null
	 */
	public function getConsoleUsage(AdapterInterface $console) {

		return array(
			'openstore recreatedb' => 'Recreate database schema and load initial fixtures.',
			'openstore updatedb' => 'Update database schema and reload initial fixtures.',
		);
	}

}

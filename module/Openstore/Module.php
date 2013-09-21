<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

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

use HTMLPurifier;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface
{

	public function init(ModuleManager $moduleManager)
	{
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
	
	public function onBootstrap(MvcEvent $e)
	{
		
		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onPreDispatch'), 100);
		$eventManager->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'), 100);
		
		$translator = $e->getApplication()->getServiceManager()->get('translator');
		//$translator->setLocale('en_US');
        //$translator->setFallbackLocale('fr_FR');    		
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
	
	public function onPreDispatch(MvcEvent $e) {
		$app      = $e->getTarget();
		
		// TODO
		$language = $e->getRouteMatch()->getParam('ui_language');
		$supported_langs = array(
			'fr' => 'fr_FR',
			'en' => 'en_US',
			'nl' => 'nl_NL',
			'de' => 'de_DE'
		);
		if ($language != '' && array_key_exists($language, $supported_langs)) {
	        $serviceManager  = $app->getServiceManager();
			$serviceManager->get('translator')->setLocale($supported_langs[$language]);
		}
		
	}

    /**
     * @inheritdoc
     */
    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                //'ZendDeveloperTools\ReportInterface' => 'ZendDeveloperTools\Report',
            ),
            'invokables' => array(
                'Model\Product'		=> 'Openstore\Model\Product',
				
				
            ),
            'factories' => array(
                'Openstore\Configuration'	=> 'Openstore\ConfigurationFactory',
				'Openstore\Service'			=> 'Openstore\ServiceFactory',
				
				'Openstore\Permission' => function($sm) {
					$permission = new Permission();
					$permission->setServiceLocator($sm);
                    return $permission;
				},
            ),
        );
    }
	

	public function getConfig()
	{
		
		$config = array_merge(
				include __DIR__ . '/config/module.config.php',
				include __DIR__ . '/config/routes.config.php',
				include __DIR__ . '/config/openstore.config.php'
		);
		return $config;
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					'Nv' => __DIR__ . '/src/Nv',
					'Smart' => __DIR__ . '/src/Smart'
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
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
			'openstore recreatedb' => 'Recreate database schema and load initial fixtures.',
			'openstore updatedb' => 'Update database schema and reload initial fixtures.',
			'akilia setup' => 'Dummy setup action.',
            'akilia syncdb' => 'Synchronize with akilia database.',
            
        );
    }
			

}

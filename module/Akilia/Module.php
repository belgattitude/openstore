<?php
namespace Akilia;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
//use Openstore\Configuration;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
//use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface;



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
            'factories' => array(
                //'Openstore\Configuration'	=> 'Openstore\ConfigurationFactory',
            ),
        );
    }
	

	public function getConfig()
	{
		
		$config = array_merge(
				include __DIR__ . '/config/module.config.php',
				include __DIR__ . '/config/routes.config.php'
		);
		return $config;
	}

	public function getAutoloaderConfig()
	{
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
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
			'akilia setup' => 'Dummy setup action.',
            'akilia syncdb' => 'Synchronize with akilia database.',
			'akilia syncmedia' => 'Synchronize product pictures with akilia.',
			'akilia syncapi' => 'Synchronize API tokens with akilia.',
			'akilia listproductpictures' => 'List all product pictures',
			'akilia archiveproductpictures' => 'Move archived product pictures (prompt)',
			'akilia checksynchro' => 'Check synchronization for errors',
        );
    }
			

}

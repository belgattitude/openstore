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
use Zend\Mvc\MvcEvent;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
//use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ConsoleUsageProviderInterface
{

	public function onBootstrap(MvcEvent $e)
	{
		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
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
                //'ZendDeveloperTools\Report'             => 'ZendDeveloperTools\Report',
            ),
            'factories' => array(
                'Openstore\Config' => function ($sm) {
                    $config = $sm->get('Configuration');
                    $config = isset($config['openstore']) ? $config['openstore'] : null;
                    return new Options($config);
                },
            ),
        );
    }
	

	public function getConfig()
	{
		return array_merge(
				include __DIR__ . '/config/module.config.php',
				include __DIR__ . '/config/routes.config.php',
				include __DIR__ . '/config/openstore.config.php'
		);
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					'Nv' => __DIR__ . '/src/Nv',
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

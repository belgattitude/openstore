<?php

use Zend\Console\Console;


$config = array(
	
    'service_manager' => [
        'aliases' => [
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service'
        ]
    ],
	
	
	// This should be an array of module namespaces used in the application.
	'modules' => array(
		//'Application',
		
		'ZfcBase',
		'ZfcUser',
		
		'ZfcRbac',
		
		// Apigility
        'ZF\Apigility',
        'ZF\Apigility\Provider',
        'AssetManager',
        'ZF\ApiProblem',
        'ZF\MvcAuth',
        'ZF\OAuth2',
        'ZF\Hal',
        'ZF\ContentNegotiation',
        'ZF\ContentValidation',
        'ZF\Rest',
        'ZF\Rpc',
        'ZF\Versioning',
        'ZF\DevelopmentMode',		
		
		
		//'AsseticBundle',
		'SolubleNormalist',
		'Openstore',
		'OpenstoreApi',
		'Akilia',
		'DoctrineModule',
		'DoctrineORMModule',
		'BjyProfiler',
		'DoctrineDataFixtureModule',
		
		
		
	),

	
	// These are various options for the listeners attached to the ModuleManager
	'module_listener_options' => array(
		// This should be an array of paths in which modules reside.
		// If a string key is provided, the listener will consider that a module
		// namespace, the value of that key the specific path to that module's
		// Module class.
		'module_paths' => array(
			'./module',
			'./vendor',
		),
		// An array of paths from which to glob configuration files after
		// modules are loaded. These effectively overide configuration
		// provided by modules themselves. Paths may use GLOB_BRACE notation.
		'config_glob_paths' => array(
			'config/autoload/{,*.}{global,local}.php',
		),
	// Whether or not to enable a configuration cache.
	// If enabled, the merged configuration will be cached and used in
	// subsequent requests.
		'config_cache_enabled' => false,
	// The key used to create the configuration cache file name.
		'config_cache_key' => 'openstore-module-cache',
	// Whether or not to enable a module class map cache.
	// If enabled, creates a module class map cache which will be used
	// by in future requests, to reduce the autoloading process.
		'module_map_cache_enabled' => false,
	// The key used to create the class map cache file name.
		'module_map_cache_key' => 'openstore-module-map-cache',
	// The path in which to cache merged configuration.
		'cache_dir' => __DIR__ . '/../data/cache',
	// Whether or not to enable modules dependency checking.
	// Enabled by default, prevents usage of modules that depend on other modules
	// that weren't loaded.
	// 'check_dependencies' => true,
	),
		// Used to create an own service manager. May contain one or more child arrays.
		//'service_listener_options' => array(
		//     array(
		//         'service_manager' => $stringServiceManagerName,
		//         'config_key'      => $stringConfigKey,
		//         'interface'       => $stringOptionalInterface,
		//         'method'          => $stringRequiredMethodName,
		//     ),
		// )
		// Initial configuration with which to seed the ServiceManager.
		// Should be compatible with Zend\ServiceManager\Config.
		// 'service_manager' => array(),
);

if (Console::isConsole()) {
	$key = array_search('Zf2Whoops', $config['modules']);
	if ($key !== false) unset($config['modules'][$key]);

	$key = array_search('ZfcRbac', $config['modules']);
	if ($key !== false) unset($config['modules'][$key]);
	
	
	
	
}

return $config;
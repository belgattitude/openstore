<?php

return array(
	'normalist' => array('cool'),
    // Factory mappings - used to define which factory to use to instantiate a particular doctrine
    // service type
    'doctrine_factories' => array(
        'cache'                 => 'DoctrineModule\Service\CacheFactory',
        'eventmanager'          => 'DoctrineModule\Service\EventManagerFactory',
        'driver'                => 'DoctrineModule\Service\DriverFactory',
        'authenticationadapter' => 'DoctrineModule\Service\Authentication\AdapterFactory',
        'authenticationstorage' => 'DoctrineModule\Service\Authentication\StorageFactory',
        'authenticationservice' => 'DoctrineModule\Service\Authentication\AuthenticationServiceFactory',
    ),

    'service_manager' => array(
        'invokables' => array(
            'DoctrineModule\Authentication\Storage\Session' => 'Zend\Authentication\Storage\Session'
        ),
        'factories' => array(
            'doctrine.cli' => 'DoctrineModule\Service\CliFactory',
        ),
        'abstract_factories' => array(
            'DoctrineModule' => 'DoctrineModule\ServiceFactory\AbstractDoctrineServiceFactory',
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'DoctrineModule\Controller\Cli' => 'DoctrineModule\Service\CliControllerFactory'
        )
    ),

    'route_manager' => array(
        'factories' => array(
            'symfony_cli' => 'DoctrineModule\Service\SymfonyCliRouteFactory',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'doctrine_cli' => array(
                    'type' => 'symfony_cli',
                )
            )
        )
    ),
);

<?php

$root = dirname(__DIR__) ;

$config = array(
    'service_manager' => [
        'aliases' => [
            //'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service'
        ]
    ],
    // This should be an array of module namespaces used in the application.
    'modules' => array(
        //'AssetManager',
        //'AsseticBundle',
        'SolubleNormalist',
        'Openstore',
        'OpenstoreApi',
        'DoctrineModule',
        'DoctrineORMModule',
        //'ZendDeveloperTools',

    ),
    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => array(
            $root . '/module',
            $root . '/vendor',
        ),
        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively overide configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
        )
    )
);


return $config;

<?php

/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */
return array(
    
    
    
    // Whether or not to enable a configuration cache.
    // If enabled, the merged configuration will be cached and used in
    // subsequent requests.
    //'config_cache_enabled' => false,
    // The key used to create the configuration cache file name.
    //'config_cache_key' => 'module_config_cache',
    // The path in which to cache merged configuration.
    //'cache_dir' =>  './data/cache',
    // ...

    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'openstore_sid', // string 	Specifies the name of the session which is used as cookie name.
                'remember_me_seconds' => 12000,
                'save_path' => dirname(__FILE__) . '/../../data/session', // 	string 	Defines the argument which is passed to the save handler.				
            /*
             * Usefull with StandardConfig instead of sessionConfig
              'cache_expire' => ini_get('session.cache_expire'),		// integer 	Specifies time-to-live for cached session pages in minutes.
              'cookie_domain' => ini_get('session.cookie_domain'),		// 	string 	Specifies the domain to set in the session cookie.
              'cookie_httponly' => ini_get('session.cookie_httponly'),	// 	boolean 	Marks the cookie as accessible only through the HTTP protocol.
              'cookie_lifetime' => ini_get('session.cookie_lifetime'),	// 	integer 	Specifies the lifetime of the cookie in seconds which is sent to the browser.
              'cookie_path' => ini_get('session.cookie_path'),			// 	string 	Specifies path to set in the session cookie.
              'cookie_secure' => ini_get('session.cookie_secure'),		// 	boolean 	Specifies whether cookies should only be sent over secure connections.
              'entropy_length' => ini_get('session.entropy_length'),		// 	integer 	Specifies the number of bytes which will be read from the file specified in entropy_file.
              'entropy_file' => ini_get('session.entropy_file'),			// 	string 	Defines a path to an external resource (file) which will be used as an additional entropy.
              'gc_maxlifetime' => ini_get('session.gc_maxlifetime'),		// 	integer 	Specifies the number of seconds after which data will be seen as ‘garbage’.
              'gc_divisor' => ini_get('session.gc_divisor'),				// 	integer 	Defines the probability that the gc process is started on every session initialization.
              'gc_probability' => ini_get('session.cookie_domain'),		// 	integer 	Defines the probability that the gc process is started on every session initialization.
              'hash_bits_per_character' => ini_get('session.hash_bits_per_character'), // 	integer 	Defines how many bits are stored in each character when converting the binary hash data.
              'remember_me_seconds' => ini_get('session.remember_me_seconds'), // 	integer 	Specifies how long to remember the session before clearing data.
              'save_path' => dirname(__FILE__) . '/../../data/session', // 	string 	Defines the argument which is passed to the save handler.
              'use_cookies' => ini_get('session.use_cookies')
             * 
             */
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        //'save_handler' => ''
        'validators' => array(
            array(
                'Zend\Session\Validator\RemoteAddr',
                'Zend\Session\Validator\HttpUserAgent',
            ),
        ),
    ),
);

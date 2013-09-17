<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Openstore;

return array(
	'service_manager' => array(
        'factories' => array(
            'Openstore\Authorize\Provider\Identity\OpenstoreDb'
                => 'Openstore\Authorize\Service\OpenstoreDbIdentityProviderServiceFactory',            
		),
		'abstract_factories' => array(
			'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
			'Zend\Log\LoggerAbstractServiceFactory',
		),
		'aliases' => array(
			'translator' => 'MvcTranslator',
		),
		
	),
	'translator' => array(
		'locale' => 'fr_FR',
		'translation_file_patterns' => array(
			array(
				'type' => 'gettext',
				'base_dir' => __DIR__ . '/../language',
				'pattern' => '%s.mo',
			),
		),
	),
	'controllers' => array(
		'invokables' => array(
			'Openstore\Controller\Index' => 'Openstore\Controller\IndexController',
			'Openstore\Controller\Store' => 'Openstore\Controller\StoreController',
			'Openstore\Controller\Search' => 'Openstore\Controller\SearchController',
			'Openstore\Controller\Shopcart' => 'Openstore\Controller\ShopcartController',
			'Openstore\Controller\Console' => 'Openstore\Controller\ConsoleController',
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions' => true,
		'doctype' => 'HTML5',
		'not_found_template' => 'error/404',
		'exception_template' => 'error/index',
		'template_map' => array(
			'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
			'openstore/index/index' => __DIR__ . '/../view/openstore/index/index.phtml',
			
			'zfc-user/user/login' => __DIR__ . '/../view/zfc-user/user/login.phtml',
			
			
			'error/404' => __DIR__ . '/../view/error/404.phtml',
			'error/index' => __DIR__ . '/../view/error/index.phtml',
			// Various snippets
			'snippets/categories_hmenu' => __DIR__ . '/../view/snippets/categories_hmenu.phtml',
			'snippets/main_carousel' => __DIR__ . '/../view/snippets/main_carousel.phtml',
		),
		'template_path_stack' => array(
			realpath(__DIR__ . '/../view'),
		),
		'strategies' => array(
			'ViewJsonStrategy',
		)
	),
	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
			),
// overriding zfc-user-doctrine-orm's config
            'zfcuser_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
			
			/**
			  'translatable_metadata_driver' => array(
			  'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
			  'cache' => 'array',
			  'paths' => array(
			  'vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity',
			  ),
			  ),
			 */
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
				//'Gedmo\Translatable\Entity' => 'translatable_metadata_driver',
				)
			)
		),
		'eventmanager' => array(
			'orm_default' => array(
				'subscribers' => array(
					'Gedmo\Timestampable\TimestampableListener',
					'Gedmo\SoftDeleteable\SoftDeleteableListener',
					//'Gedmo\Translatable\TranslatableListener',
					'Gedmo\Blameable\BlameableListener',
					// 'Gedmo\Loggable\LoggableListener',
					'Gedmo\Sluggable\SluggableListener',
					// 'Gedmo\Sortable\SortableListener',
					'Gedmo\Tree\TreeListener',
				),
			),
		)
	),
	'data-fixture' => array(
		'location' => __DIR__ . '/../src/Openstore/Fixtures',
	),
	/*
	  'di' => array(
	  'Openstore\Catalog\Browser\BrowserAbstract' => array(
	  'parameters' => array(
	  'adapter'  => 'Zend\Db\Adapter\Adapter',
	  ),
	  ),
	  )
	 */
   'zfcuser' => array(
        // telling ZfcUser to use our own class
        'user_entity_class'       => 'Openstore\Entity\User',
        // telling ZfcUserDoctrineORM to skip the entities it defines
        'enable_default_entities' => false,
	   
    ),

    'bjyauthorize' => array(
        // Using the authentication identity provider, which basically reads the roles from the auth service's identity
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',

        'role_providers'        => array(
            // using an object repository (entity repository) to load all roles into our ACL
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'Openstore\Entity\Role',
             ),
        ),
    ),	
);

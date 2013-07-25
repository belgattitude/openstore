<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Openstore;

$supported_languages = array('en', 'nl', 'fr', 'zh', 'de', 'es', 'it');
$default_language = 'en';
$default_pricelist = 'FR';
$browser_language = \Locale::getPrimaryLanguage(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']));

return array(
	'console' => array(
        'router' => array(
            'routes' => array(
				'setup' => array(
						'options' => array(
							'route'    => 'akilia setup',
							'defaults' => array(
								//'__NAMESPACE__' => 'Openstore\Controller',
								'controller' => 'Openstore\Controller\Console',
								'action'     => 'setup'
							)
						)
				),
				
				'openstore-updatedb' => array(
						'options' => array(
							'route'    => 'openstore updatedb',
							'defaults' => array(
								//'__NAMESPACE__' => 'Openstore\Controller',
								'controller' => 'Openstore\Controller\Console',
								'action'     => 'updatedb'
							)
						)
				),							
				'openstore-recreatedb' => array(
						'options' => array(
							'route'    => 'openstore recreatedb',
							'defaults' => array(
								//'__NAMESPACE__' => 'Openstore\Controller',
								'controller' => 'Openstore\Controller\Console',
								'action'     => 'recreatedb'
							)
						)
				),			
				
				'akilia-syncdb' => array(
						'options' => array(
							'route'    => 'akilia syncdb',
							'defaults' => array(
								//'__NAMESPACE__' => 'Openstore\Controller',
								'controller' => 'Openstore\Controller\Console',
								'action'     => 'akiliasyncdb'
							)
						)
				),			
            )
        )
    ),	
	
    'router' => array(
        'routes' => array(
            'store' => array(
				'type'    => 'Zend\Mvc\Router\Http\Segment',
				  'options' => array(
					  //'route'		=> '/store[:language[/store[/:pricelist]]]',
					  'route'		=> '[/:ui_language]/store[/pricelist/:pricelist]',
					   //'route'       => '/[:lang]/store[:pricelist]',
					  
					   'constraints' => array(
							'ui_language' => '(' . join('|', $supported_languages) .')',
							//'pricelist' => '[A-Za-z0-9]{0,5}',
					    ),
					   'defaults' => array(
							'__NAMESPACE__' => 'Openstore\Controller',
							'controller'    => 'Index',
							'action'        => 'index',
							'ui_language'		=> 
									in_array($browser_language, $supported_languages) ? $browser_language : $default_language,
						    'pricelist'		=> $default_pricelist
					   ),
				   ),				
                'may_terminate' => true,
                'child_routes' => array(

                    'product' => array(
                        'type' => 'segment',
						'options' => array (
							'route' => '/product/:product_id',
							'defaults' => array (
								'action' => 'product',
							)
						),
                    ),		
					
                    'browse' => array(
                        'type' => 'segment',
						'options' => array (
							'route' => '/browse[/filter/:browse_filter][/brand/:brand_reference][/category/:category_reference]',
							'defaults' => array (
								'action' => 'index',
							)
						),
                    ),				
					
                    'category' => array(
                        'type' => 'segment',
						'options' => array (
							'route' => '/category[/:category_reference]',
							'defaults' => array (
								'action' => 'index',
							)
						),
                    ),				
                    'brand' => array(
                        'type' => 'segment',
						'options' => array (
							'route' => '/brand[/:brand_reference]',
							'defaults' => array (
								'action' => 'index',
							)
						),
                    ),				
					
                ),
            ),
            'shopcart' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/shopcart',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Openstore\Controller',
                        'controller'    => 'Shopcart',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
	
	
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Openstore\Controller\Index' => 'Openstore\Controller\IndexController',
			'Openstore\Controller\Shopcart' => 'Openstore\Controller\ShopcartController',
			'Openstore\Controller\Console' => 'Openstore\Controller\ConsoleController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'openstore/index/index'    => __DIR__ . '/../view/openstore/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
			
			// Various snippets
			'snippets/categories_hmenu' => __DIR__ . '/../view/snippets/categories_hmenu.phtml',
			'snippets/main_carousel'	=> __DIR__ . '/../view/snippets/main_carousel.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
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
	
);

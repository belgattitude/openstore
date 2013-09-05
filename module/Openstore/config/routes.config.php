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
						'route' => 'akilia setup',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Openstore\Controller\Console',
							'action' => 'setup'
						)
					)
				),
				'openstore-updatedb' => array(
					'options' => array(
						'route' => 'openstore updatedb',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Openstore\Controller\Console',
							'action' => 'updatedb'
						)
					)
				),
				'openstore-recreatedb' => array(
					'options' => array(
						'route' => 'openstore recreatedb',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Openstore\Controller\Console',
							'action' => 'recreatedb'
						)
					)
				),
				'akilia-syncdb' => array(
					'options' => array(
						'route' => 'akilia syncdb',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Openstore\Controller\Console',
							'action' => 'akiliasyncdb'
						)
					)
				),
			)
		)
	),
	'router' => array(
		'routes' => array(
			
			'store' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					//'route'		=> '/store[:language[/store[/:pricelist]]]',
					'route' => '[/:ui_language]/store[/pricelist/:pricelist]',
					//'route'       => '/[:lang]/store[:pricelist]',
					'constraints' => array(
						'ui_language' => '(' . join('|', $supported_languages) . ')',
					//'pricelist' => '[A-Za-z0-9]{0,5}',
					),
					'defaults' => array(
						'__NAMESPACE__' => 'Openstore\Controller',
						'controller' => 'Store',
						'action' => 'index',
						'ui_language' =>
						in_array($browser_language, $supported_languages) ? $browser_language : $default_language,
						'pricelist' => $default_pricelist
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'product' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/product/:product_id',
							'defaults' => array(
								'action' => 'product',
							)
						),
					),
					'browse' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/browse[/filter/:filter][/brands/:brands][/categories/:categories][/page/:page][/limit/:limit][/sortBy/:sortBy][/sortDir/:sortDir]',
							'defaults' => array(
								'action'	=> 'browse',
								//'query'		=> null,
								'filter'	=> null,
								'brands'	=> '',
								'categories'=> '',
								'page'		=> 1,
								//'limit'	=> 10, // better in the controller
								'sortBy'	=> "test",
								'sortDir'	=> "ASC",								
							),
						),
					),
					/*
					'category' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/category[/:category_reference]',
							'defaults' => array(
								'action' => 'index',
							)
						),
					),
					'brand' => array(
						'type' => 'segment',
						'options' => array(
							'route' => '/brand[/:brand_reference]',
							'defaults' => array(
								'action' => 'index',
							)
						),
					),*/
				),
			),
			'shopcart' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/shopcart',
					'defaults' => array(
						'__NAMESPACE__' => 'Openstore\Controller',
						'controller' => 'Shopcart',
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'default' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/[:controller[/:action]]',
							'constraints' => array(
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
							),
						),
					),
				),
			),
		),
	)
);

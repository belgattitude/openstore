<?php

namespace Akilia;


return array(
	'console' => array(
		'router' => array(
			'routes' => array(
				'archiveproductpictures' => array(
					'options' => array(
						'route' => 'akilia archiveproductpictures',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Akilia\Controller\Console',
							'action' => 'archiveproductpictures'
						)
					)
				),
				
				'listproductpictures' => array(
					'options' => array(
						'route' => 'akilia listproductpictures',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Akilia\Controller\Console',
							'action' => 'listproductpictures'
						)
					)
				),

				'syncdb' => array(
					'options' => array(
						'route' => 'akilia syncdb',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Akilia\Controller\Console',
							'action' => 'syncdb'
						)
					)
				),
				'syncapi' => array(
					'options' => array(
						'route' => 'akilia syncapi',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Akilia\Controller\Console',
							'action' => 'syncapi'
						)
					)
				),				
				
				'syncmedia' => array(
					'options' => array(
						'route' => 'akilia syncmedia',
						'defaults' => array(
							//'__NAMESPACE__' => 'Openstore\Controller',
							'controller' => 'Akilia\Controller\Console',
							'action' => 'syncmedia'
						)
					)
				),				
			)
		)
	),
	'router' => array(
		'routes' => array(
			'akilia' => array(
					   'type' => 'Zend\Mvc\Router\Http\Literal',
					   'options' => array(
						   'route' => '/akilia',
						   'defaults' => array(
							   'controller' => 'Akilia\Controller\Index',
							   'action' => 'index',
						   ),
					   ),
				   ),			
		)
	)
);

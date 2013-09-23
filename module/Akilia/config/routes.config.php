<?php

namespace Akilia;


return array(
	'console' => array(
		'router' => array(
			'routes' => array(

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

<?php

namespace OpenstoreApi;


return array(
	'router' => array(
		'routes' => array(
			'api' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/api',
					'defaults' => array(
						'__NAMESPACE__' => 'OpenstoreApi/Controller'
					)
				),
				'may_terminate' => true,
				'child_routes' => array(
					'media' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/media[/:id]',
							'defaults' => array(
								'controller' => 'Media'
							)
						)
					),
				),
			)
		)
	)
);

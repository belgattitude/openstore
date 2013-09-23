<?php

namespace Akilia;

return array(
	'service_manager' => array(
        'factories' => array(
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
			'Akilia\Controller\Console'	=> 'Akilia\Controller\ConsoleController',
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions' => true,
		'doctype' => 'HTML5',
		'not_found_template' => 'error/404',
		'exception_template' => 'error/index',
		'template_map' => array(
		),
		'template_path_stack' => array(
			realpath(__DIR__ . '/../view'),
		),
		'strategies' => array(
			'ViewJsonStrategy',
		)
	),
);

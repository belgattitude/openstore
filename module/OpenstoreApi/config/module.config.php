<?php

namespace OpenstoreApi;

return array(
	'errors' => array(
		'show_exceptions' => array(
			'message' => true,
			'trace' => true
		)
	),
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
		'locale' => 'en_GB',
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
			'OpenstoreApi\Controller\ProductMedia' => 'OpenstoreApi\Controller\ProductMediaController',
			'OpenstoreApi\Controller\ProductCatalog' => 'OpenstoreApi\Controller\ProductCatalogController',
			'OpenstoreApi\Controller\ProductBrand' => 'OpenstoreApi\Controller\ProductBrandController',
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions' => true,
		'strategies' => array(
			'ViewJsonStrategy',
		)
	)
);

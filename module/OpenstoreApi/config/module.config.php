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
            'OpenstoreApi\Controller\ProductStock' => 'OpenstoreApi\Controller\ProductStockController',
            'OpenstoreApi\Controller\ProductBrand' => 'OpenstoreApi\Controller\ProductBrandController',
            'OpenstoreApi\Controller\NammProductCatalog' => 'OpenstoreApi\Controller\NammProductCatalogController',
            'OpenstoreApi\Controller\Generic' => 'OpenstoreApi\Controller\GenericController',
        ),
    ),
    /*
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
      'strategies' => array(
      'ViewJsonStrategy',
      )
      ),
     */
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        /*
        'template_map' => array(
            'namm_item_v2011.1' => __DIR__ . '/../view/namm_b2b/namm_item_v2011.1.phtml',
            'test' => __DIR__ . '/../view/test.phtml',
        ),*/
        'template_path_stack' => array(
            //realpath(__DIR__ . '/../../public/themes'),
            realpath(__DIR__ . '/../view'),
            
        ),
    )
);

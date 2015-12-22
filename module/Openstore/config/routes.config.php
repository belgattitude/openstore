<?php

namespace Openstore;

$supported_languages = array('en', 'nl', 'fr', 'zh', 'de', 'es', 'it');
$default_language = 'en';
$default_pricelist = 'FR';

if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $browser_language = \Locale::getPrimaryLanguage(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']));
} else {
    $browser_language = $default_language;
}

return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'openstore:schema-core:create' => array(
                    'options' => array(
                        'route' => 'openstore:schema-core:create [--dump-sql]',
                        'defaults' => array(
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-create'
                        )
                    )
                ),
                'openstore:schema-core:recreate-extra' => array(
                    'options' => array(
                        'route' => 'openstore:schema-core:recreate-extra [--dump-sql]',
                        'defaults' => array(
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-recreate-extra'
                        )
                    )
                ),
                'openstore:schema-core:update' => array(
                    'options' => array(
                        'route' => 'openstore:schema-core:update [--dump-sql]',
                        'defaults' => array(
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-update'
                        )
                    )
                ),
                'openstore:schema-core:load' => array(
                    'options' => array(
                        'route' => 'openstore:schema-core:load [--dump-sql]',
                        'defaults' => array(
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-load'
                        )
                    )
                ),
                

                'openstore-updateproductslug' => array(
                    'options' => array(
                        'route' => 'openstore updateproductslug',
                        'defaults' => array(
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'updateproductslug'
                        )
                    )
                ),
                'openstore-build-all-reload' => array(
                    'options' => array(
                        'route' => 'openstore build-all-reload',
                        'defaults' => array(
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'buildallreload'
                        )
                    )
                ),
                'openstore-relocategroupcateg' => array(
                    'options' => array(
                        'route' => 'openstore relocategroupcateg',
                        'defaults' => array(
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'relocategroupcateg'
                        )
                    )
                ),
                'openstore-clearcache' => array(
                    'options' => array(
                        'route' => 'openstore clearcache',
                        'defaults' => array(
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'clearcache'
                        )
                    )
                ),
                'openstore-clearmediacache' => array(
                    'options' => array(
                        'route' => 'openstore clearmediacache',
                        'defaults' => array(
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'clearmediacache'
                        )
                    )
                ),
            )
        )
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Openstore\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'media' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/media',
                    'defaults' => array(
                        'controller' => 'Openstore\Controller\Media',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'preview' => array(
                        'type' => 'regex',
                        'options' => array(
                            // i.e: /public/media/preview/picture/<media_id>_800x800-95.png
                            //'regex' => '/preview/((<type>(picture|sound))/)((<resolution>([0-9]+x[0-9]+)))(\-(<quality>([0-9]+))/)(?<id>[0-9]+)?(\.(?<format>(jpg|png|gif)))?',
                            'regex' => '/preview/((?<type>(picture|productpicture|sound))/)((?<options>([0-9A-Za-z-_]+))/)((?<prefix>[0-9]{1,2})/)(?<media_id>[0-9]+)(\_(?<filemtime>[0-9]+))?(\.(?<format>(jpg|png|gif|flv)))?',
                            'spec' => '/preview/%type%/%options%/%prefix%/%media_id%%filemtime%.%format%',
                            'defaults' => array(
                                'action' => 'preview',
                                'filemtime' => null,
                                'format' => 'jpg'
                            )
                        )
                    ),
                    'dynamic' => array(
                        'type' => 'regex',
                        'options' => array(
                            // i.e: /public/media/dynamic/product/170x200/12722.jpg
                            'regex' => '/dynamic/((?<type>(product|brand|serie))/)?((?<resolution>([0-9]+x[0-9]+))/)?(?<id>[0-9]+)(\.(?<format>(jpg|png)))?',
                            'spec' => '/dynamic/%type%/%resolution%/%id%.%format%',
                            'defaults' => array(
                                'action' => 'picture',
                                'resolution' => '1024x768',
                                'quality' => '90',
                                'format' => 'jpg'
                            )
                        )
                    ),
                    'picture' => array(
                        'type' => 'regex',
                        'options' => array(
                            // i.e: /public/media/picture/product/14555_800x800-95.png
                            'regex' => '/picture/((?<type>(product|brand|serie))/)?(?<id>[0-9]+)(\_(?<resolution>([0-9]+x[0-9]+)))?(\-(?<quality>([0-9]+)))?(\.(?<format>(jpg|png|gif)))?',
                            'spec' => '/picture/%type%/%id%_%resolution%-%quality%.%format%',
                            'defaults' => array(
                                'action' => 'picture',
                                'resolution' => '1024x768',
                                'quality' => '90',
                                'format' => 'jpg'
                            )
                        )
                    ),
                ),
            ),
            'store' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    //'route'        => '/store[:language[/store[/:pricelist]]]',
                    'route' => '[/:ui_language]/store[/pricelist/:pricelist]',
                    //'route'       => '/[:lang]/store[:pricelist]',
                    'constraints' => array(
                        'ui_language' => '(' . join('|', $supported_languages) . ')',
                        'pricelist' => '[A-Za-z0-9]{0,5}',
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
                    'search' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/search[/:action]',
                            'constraints' => array(
                                'controller' => 'Api',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                //'module' => 'Front',
                                //'controller' => 'Front\Controller\Index',
                                //'action' => 'index'
                                //'__NAMESPACE__' => 'Openstore\Controller',
                                'controller' => 'Search',
                            )
                        )
                    ),
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
                                'action' => 'browse',
                                //'query'        => null,
                                'filter' => null,
                                'brands' => '',
                                'categories' => '',
                                'page' => 1,
                                //'limit'    => 10, // better in the controller
                                'sortBy' => "test",
                                'sortDir' => "ASC",
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
                  ), */
                ),
            ),
            'shopcart' => array(
                'type' => 'Segment',
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
                    'actions' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            )
                        )
                    ),
                )
            ),
            /**
             * BJYAuthorize
             */
            'zfcuser' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '', // the route is void isntead of default 'user'
                ),
            ),
            'zfcuser/login' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'zfcuser',
                        'action' => 'login',
                    ),
                ),
            ),
            'zfcuser/authenticate' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/authenticate',
                    'defaults' => array(
                        'controller' => 'zfcuser',
                        'action' => 'authenticate',
                    ),
                ),
            ),
            'zfcuser/logout' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => 'zfcuser',
                        'action' => 'logout',
                    ),
                ),
            ),
            'zfcuser/register' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/register',
                    'defaults' => array(
                        'controller' => 'zfcuser',
                        'action' => 'register',
                    ),
                ),
            ),
            'zfcuser/changepassword' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/change-password',
                    'defaults' => array(
                        'controller' => 'zfcuser',
                        'action' => 'changepassword',
                    ),
                ),
            ),
            'zfcuser/changeemail' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/change-email',
                    'defaults' => array(
                        'controller' => 'zfcuser',
                        'action' => 'changeemail',
                    ),
                ),
            ),
        ),
    )
);

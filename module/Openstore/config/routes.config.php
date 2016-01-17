<?php

namespace Openstore;

$supported_languages = ['en', 'nl', 'fr', 'zh', 'de', 'es', 'it'];
$default_language = 'en';
$default_pricelist = 'FR';

if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $browser_language = \Locale::getPrimaryLanguage(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']));
} else {
    $browser_language = $default_language;
}

return [
    'console' => [
        'router' => [
            'routes' => [
                'openstore:schema-core:create' => [
                    'options' => [
                        'route' => 'openstore:schema-core:create [--dump-sql]',
                        'defaults' => [
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-create'
                        ]
                    ]
                ],
                'openstore:schema-core:recreate-extra' => [
                    'options' => [
                        'route' => 'openstore:schema-core:recreate-extra [--dump-sql]',
                        'defaults' => [
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-recreate-extra'
                        ]
                    ]
                ],
                'openstore:schema-core:update' => [
                    'options' => [
                        'route' => 'openstore:schema-core:update [--dump-sql]',
                        'defaults' => [
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-update'
                        ]
                    ]
                ],
                'openstore:schema-core:load' => [
                    'options' => [
                        'route' => 'openstore:schema-core:load [--dump-sql]',
                        'defaults' => [
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'schema-core-load'
                        ]
                    ]
                ],


                'openstore-updateproductslug' => [
                    'options' => [
                        'route' => 'openstore updateproductslug',
                        'defaults' => [
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'updateproductslug'
                        ]
                    ]
                ],
                'openstore-build-all-reload' => [
                    'options' => [
                        'route' => 'openstore build-all-reload',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'buildallreload'
                        ]
                    ]
                ],
                'openstore-relocategroupcateg' => [
                    'options' => [
                        'route' => 'openstore relocategroupcateg',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'relocategroupcateg'
                        ]
                    ]
                ],
                'openstore-clearcache' => [
                    'options' => [
                        'route' => 'openstore clearcache',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'clearcache'
                        ]
                    ]
                ],
                'openstore-clearmediacache' => [
                    'options' => [
                        'route' => 'openstore clearmediacache',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Openstore\Controller\Console',
                            'action' => 'clearmediacache'
                        ]
                    ]
                ],
            ]
        ]
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'Openstore\Controller\Index',
                        'action' => 'index',
                    ],
                ],
            ],
            'media' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/media',
                    'defaults' => [
                        'controller' => 'Openstore\Controller\Media',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'preview' => [
                        'type' => 'regex',
                        'options' => [
                            // i.e: /public/media/preview/picture/<media_id>_800x800-95.png
                            //'regex' => '/preview/((<type>(picture|sound))/)((<resolution>([0-9]+x[0-9]+)))(\-(<quality>([0-9]+))/)(?<id>[0-9]+)?(\.(?<format>(jpg|png|gif)))?',
                            'regex' => '/preview/((?<type>(picture|productpicture|sound))/)((?<options>([0-9A-Za-z-_]+))/)((?<prefix>[0-9]{1,2})/)(?<media_id>[0-9]+)(\_(?<filemtime>[0-9]+))?(\.(?<format>(jpg|png|gif|flv)))?',
                            'spec' => '/preview/%type%/%options%/%prefix%/%media_id%%filemtime%.%format%',
                            'defaults' => [
                                'action' => 'preview',
                                'filemtime' => null,
                                'format' => 'jpg'
                            ]
                        ]
                    ],
                    'dynamic' => [
                        'type' => 'regex',
                        'options' => [
                            // i.e: /public/media/dynamic/product/170x200/12722.jpg
                            'regex' => '/dynamic/((?<type>(product|brand|serie))/)?((?<resolution>([0-9]+x[0-9]+))/)?(?<id>[0-9]+)(\.(?<format>(jpg|png)))?',
                            'spec' => '/dynamic/%type%/%resolution%/%id%.%format%',
                            'defaults' => [
                                'action' => 'picture',
                                'resolution' => '1024x768',
                                'quality' => '90',
                                'format' => 'jpg'
                            ]
                        ]
                    ],
                    'picture' => [
                        'type' => 'regex',
                        'options' => [
                            // i.e: /public/media/picture/product/14555_800x800-95.png
                            'regex' => '/picture/((?<type>(product|brand|serie))/)?(?<id>[0-9]+)(\_(?<resolution>([0-9]+x[0-9]+)))?(\-(?<quality>([0-9]+)))?(\.(?<format>(jpg|png|gif)))?',
                            'spec' => '/picture/%type%/%id%_%resolution%-%quality%.%format%',
                            'defaults' => [
                                'action' => 'picture',
                                'resolution' => '1024x768',
                                'quality' => '90',
                                'format' => 'jpg'
                            ]
                        ]
                    ],
                ],
            ],
            'store' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    //'route'        => '/store[:language[/store[/:pricelist]]]',
                    'route' => '[/:ui_language]/store[/pricelist/:pricelist]',
                    //'route'       => '/[:lang]/store[:pricelist]',
                    'constraints' => [
                        'ui_language' => '(' . implode('|', $supported_languages) . ')',
                        'pricelist' => '[A-Za-z0-9]{0,5}',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Openstore\Controller',
                        'controller' => 'Store',
                        'action' => 'index',
                        'ui_language' =>
                        in_array($browser_language, $supported_languages) ? $browser_language : $default_language,
                        'pricelist' => $default_pricelist
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'search' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/search[/:action]',
                            'constraints' => [
                                'controller' => 'Api',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                //'module' => 'Front',
                                //'controller' => 'Front\Controller\Index',
                                //'action' => 'index'
                                //'__NAMESPACE__' => 'Openstore\Controller',
                                'controller' => 'Search',
                            ]
                        ]
                    ],
                    'product' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/product/:product_id',
                            'defaults' => [
                                'action' => 'product',
                            ]
                        ],
                    ],
                    'browse' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/browse[/filter/:filter][/brands/:brands][/categories/:categories][/page/:page][/limit/:limit][/sortBy/:sortBy][/sortDir/:sortDir]',
                            'defaults' => [
                                'action' => 'browse',
                                //'query'        => null,
                                'filter' => null,
                                'brands' => '',
                                'categories' => '',
                                'page' => 1,
                                //'limit'    => 10, // better in the controller
                                'sortBy' => "test",
                                'sortDir' => "ASC",
                            ],
                        ],
                    ],
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
                ],
            ],
            'shopcart' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/shopcart',
                    'defaults' => [
                        '__NAMESPACE__' => 'Openstore\Controller',
                        'controller' => 'Shopcart',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'actions' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ]
                        ]
                    ],
                ]
            ],
            /**
             * BJYAuthorize
             */
            'zfcuser' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '', // the route is void isntead of default 'user'
                ],
            ],
            'zfcuser/login' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => 'zfcuser',
                        'action' => 'login',
                    ],
                ],
            ],
            'zfcuser/authenticate' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/authenticate',
                    'defaults' => [
                        'controller' => 'zfcuser',
                        'action' => 'authenticate',
                    ],
                ],
            ],
            'zfcuser/logout' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => 'zfcuser',
                        'action' => 'logout',
                    ],
                ],
            ],
            'zfcuser/register' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/register',
                    'defaults' => [
                        'controller' => 'zfcuser',
                        'action' => 'register',
                    ],
                ],
            ],
            'zfcuser/changepassword' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/change-password',
                    'defaults' => [
                        'controller' => 'zfcuser',
                        'action' => 'changepassword',
                    ],
                ],
            ],
            'zfcuser/changeemail' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/change-email',
                    'defaults' => [
                        'controller' => 'zfcuser',
                        'action' => 'changeemail',
                    ],
                ],
            ],
        ],
    ]
];

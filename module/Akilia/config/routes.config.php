<?php

namespace Akilia;

return [
    'console' => [
        'router' => [
            'routes' => [
                'archiveproductpictures' => [
                    'options' => [
                        'route' => 'akilia archiveproductpictures',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'archiveproductpictures'
                        ]
                    ]
                ],

                'listproductpictures' => [
                    'options' => [
                        'route' => 'akilia listproductpictures',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'listproductpictures'
                        ]
                    ]
                ],
                'syncdb' => [
                    'options' => [
                        'route' => 'akilia syncdb',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'syncdb'
                        ]
                    ]
                ],
                'geocodecustomers' => [
                    'options' => [
                        'route' => 'akilia geocodecustomers',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'geocodecustomers'
                        ]
                    ]

                ],
                'syncapi' => [
                    'options' => [
                        'route' => 'akilia syncapi',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'syncapi'
                        ]
                    ]
                ],

                'syncstock' => [
                    'options' => [
                        'route' => 'akilia syncstock',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'syncstock'
                        ]
                    ]
                ],


                'syncmedia' => [
                    'options' => [
                        'route' => 'akilia syncmedia',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'syncmedia'
                        ]
                    ]
                ],
                'checksynchro' => [
                    'options' => [
                        'route' => 'akilia checksynchro',
                        'defaults' => [
                            //'__NAMESPACE__' => 'Openstore\Controller',
                            'controller' => 'Akilia\Controller\Console',
                            'action' => 'checksynchro'
                        ]
                    ]
                ],
            ]
        ]
    ],
    'router' => [
        'routes' => [
            'akilia' => [
                       'type' => 'Zend\Mvc\Router\Http\Literal',
                       'options' => [
                           'route' => '/akilia',
                           'defaults' => [
                               'controller' => 'Akilia\Controller\Index',
                               'action' => 'index',
                           ],
                       ],
                   ],
        ]
    ]
];

<?php

namespace OpenstoreApi;

return [
    'router' => [
        'routes' => [
            'api' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/api',
                    'defaults' => [
                        '__NAMESPACE__' => 'OpenstoreApi/Controller'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'restful' => [
                        'type' => 'Segment',
                        'options' => [
                            'route'       => '/:controller[.:format][/:id]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                                'format' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'format'    => 'json',
                            ]
                        ],
                    ]

                ],
            ]
        ]
    ]
];

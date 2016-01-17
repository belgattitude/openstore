<?php

return [
    'controllers' => [
        'invokables' => [
            'SolubleNormalist\Controller\Console' => 'SolubleNormalist\Controller\ConsoleController'
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
                'normalist_generate_models' => [
                    'options' => [
                        'route' => 'normalist generate-models',
                        'defaults' => [
                            'controller' => 'SolubleNormalist\Controller\Console',
                            'action' => 'generatemodels'
                        ]
                    ]
                ]
            ]
        ]
    ]
];

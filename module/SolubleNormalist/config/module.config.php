<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'SolubleNormalist\Controller\Console' => 'SolubleNormalist\Controller\ConsoleController'
        )
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'normalist_generate_models' => array(
                    'options' => array(
                        'route' => 'normalist generate-models',
                        'defaults' => array(
                            'controller' => 'SolubleNormalist\Controller\Console',
                            'action' => 'generatemodels'
                        )
                    )
                )
            )
        )
    )
);

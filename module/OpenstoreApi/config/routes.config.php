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
                    'restful' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route'       => '/:controller[.:format][/:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                                'format' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'format'    => 'json',
                            )
                        ),
                    )
                                    
                ),
            )
        )
    )
);

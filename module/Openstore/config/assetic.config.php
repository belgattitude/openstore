<?php

//namespace Openstore;
//die('cool');
return [
    'assetic_configuration' => [
        'debug' => true,
        'buildOnRequest' => false,
        'default' => [
            'assets' => [
                '@bootstrap_base_css',
                '@bootstrap_base_js',
            ],
            'options' => [
                'mixin' => true
            ],
        ],
        'routes' => [
            'test' => [
                '@base_js',
                '@base_css',
            ],
        ],
        'webPath' => realpath('public/assets/builds'),
        'basePath' => 'assets',
        'modules' => [
            'openstore' => [
                //'root_path' => __DIR__ . '/../assets',
                'root_path' => realpath('public/assets'),
                'collections' => [
                    'bootstrap_base_js' => [
                        'assets' => [
                            'vendor/bootstrap/dist/css/bootstrap.min.css',
                        //'css/base_style.css',
                        ],
                        'filters' => [
                            'CssRewriteFilter' => [
                                'name' => 'Assetic\Filter\CssRewriteFilter'
                            ]
                        ],
                    ],
                    'bootstrap_base_css' => [
                        'assets' => [
                            'vendor/jquery/dist/jquery.min.js',
                            'vendor/bootstrap/dist/js/bootstrap.min.js',
                        ]
                    ],
                    'base_images' => [
                        'assets' => [
                            'images/*.png',
                            'images/*.ico',
                        ],
                        'options' => [
                            'move_raw' => true,
                        ]
                    ],
                ],
            ],
        ],
    ],
];

<?php

//namespace Openstore;
//die('cool');
return array(
					
   'assetic_configuration' => array(
        'debug' => true,
        'buildOnRequest' => false,

        'default' => array(
            'assets' => array(
                '@bootstrap_base_css',
				'@bootstrap_base_js',
            ),
            'options' => array(
                'mixin' => true
            ),
        ),	   
	   
        'routes' => array(
            'test' => array(
                '@base_js',
                '@base_css',
            ),
        ),
	   

        'webPath' => realpath('public/assets/builds'),
        'basePath' => 'assets',	   
	   
        'modules' => array(
            'openstore' => array(
                //'root_path' => __DIR__ . '/../assets',
				'root_path' => realpath('public/assets'),
                'collections' => array(
                    'bootstrap_base_js' => array(
                        'assets' => array(
                            'vendor/bootstrap/dist/css/bootstrap.min.css',
							//'css/base_style.css',
                        ),
                        'filters' => array(
                            'CssRewriteFilter' => array(
                                'name' => 'Assetic\Filter\CssRewriteFilter'
                            )
                        ),
                    ),

                    'bootstrap_base_css' => array(
                        'assets' => array(
							'vendor/jquery/dist/jquery.min.js',
                            'vendor/bootstrap/dist/js/bootstrap.min.js',
                        )
                    ),

                    'base_images' => array(
                        'assets' => array(
                            'images/*.png',
                            'images/*.ico',
                        ),
                        'options' => array(
                            'move_raw' => true,
                        )
                    ),
                ),
            ),
        ),
    ),	
);

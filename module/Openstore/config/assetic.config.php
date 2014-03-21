<?php

//namespace Openstore;
//die('cool');
return array(
					
   'assetic_configuration' => array(
        'debug' => true,
        'buildOnRequest' => true,

        'default' => array(
            'assets' => array(
                '@base_css',
				'@base_js',
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
	   

        'webPath' => realpath('public/assets'),
        'basePath' => 'assets',	   
	   
        'modules' => array(
            'openstore' => array(
                'root_path' => __DIR__ . '/../assets',
                'collections' => array(
                    'base_css' => array(
                        'assets' => array(
                            'resources/bootstrap/dist/css/bootstrap.min.css',
							'css/base_style.css',
                        ),
                        'filters' => array(
                            'CssRewriteFilter' => array(
                                'name' => 'Assetic\Filter\CssRewriteFilter'
                            )
                        ),
                    ),

                    'base_js' => array(
                        'assets' => array(
							'resources/jquery/jquery.min.js',
                            'resources/bootstrap/dist/js/bootstrap.min.js',
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

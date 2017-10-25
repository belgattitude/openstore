<?php

return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\Mysqli\Driver',
                //'pdo' => 'AdapterResourceFactory'
                'params' => array(
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'user' => 'user',
                    'password' => 'password',
                    'dbname' => 'openstore_production',
                    'charset' => 'UTF8',
                    //'charset' => 'utf8mb4'
                )
            )
        )
    )
);

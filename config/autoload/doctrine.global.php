<?php

return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\Mysqli\Driver',
                //'pdo' => 'AdapterResourceFactory'
                'params' => array(
                    'host' => '192.168.32.11',
                    'port' => '3306',
                    'user' => 'root',
                    'password' => 'intelart2009',
                    'dbname' => 'openstore_production',
                    'charset' => 'UTF8',
                    //'charset' => 'utf8mb4'
                )
            )
        )
    )
);

<?php

namespace SolubleNormalist\Service;

use Soluble\Normalist\Synthetic\TableManager;
use Soluble\Normalist\Driver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class TableManagerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return TableManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $config = $serviceLocator->get('Config');
		var_dump($config['normalist']);
		$adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		$options = array(
			'alias'		=> 'openstore',
			'path'		=> null,
			'version'	=> '1.0.0'
		);
        $driver = new Driver\ZeroConfDriver($adapter, $options);
        $tm = new TableManager($driver);
        return $tm;
		
    }
}

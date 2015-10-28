<?php
namespace SolubleNormalist\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SolubleNormalist\Service\Exception;

class NormalistDriverFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return TableManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $default_connection = 'default';

        $config = $serviceLocator->get('Config');
        if (!array_key_exists('normalist', $config)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Missing configuration key 'normalist' in your module config.");
        }

        if (!array_key_exists($default_connection, $config['normalist'])) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Missing configuration key ['normalist'][$default_connection] in your module config.");
        }

        if (!array_key_exists('adapter', $config['normalist'][$default_connection])) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Missing configuration key ['normalist'][$default_connection]['adapter'] in your module config.");
        }

        if (!array_key_exists('driver', $config['normalist'][$default_connection])) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Missing configuration key ['normalist'][$default_connection]['driver'] in your module config.");
        }

        $nConfig = $config['normalist'][$default_connection];


        if (!is_array($nConfig['adapter']) || $nConfig['adapter'] === null) {
            $adapterLocator = 'Zend\Db\Adapter\Adapter';
        } else {
            $adapterLocator = $nConfig['adapter']['adapterLocator'];
        }
        if (!$serviceLocator->has($adapterLocator)) {
            throw new Exception\RuntimeException(__METHOD__ . " adapterLocator '$adapterLocator' is not available through serviceLocator");
        }
        $adapter = $serviceLocator->get($adapterLocator);

        $driverClass = $nConfig['driver']['driverClass'];
        if ($driverClass == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Missing driverClass parameter in your module config");
        }
        if (!class_exists($driverClass)) {
            throw new Exception\RuntimeException(__METHOD__ . " driverClass value '$driverClass' cannot be loaded");
        }


        $params = $nConfig['driver']['params'];
        if (!is_array($params)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Driver params must be an array.");
        }
        if (!array_key_exists('path', $params)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Missing path in driver params.");
        }
        $path = $params['path'];

        if (!is_dir($path)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Normalist model path does not exists '$path'.");
        }

        if (!array_key_exists('alias', $params) || trim($params['alias']) == "") {
            $params['alias'] = $default_connection;
        }

        $driver = new $driverClass($adapter, $params);
        return $driver;
    }
}

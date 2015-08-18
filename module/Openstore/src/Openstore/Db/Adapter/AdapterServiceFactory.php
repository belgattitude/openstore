<?php

namespace Openstore\Db\Adapter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;
use Zend\Log\Writer;
use BjyProfiler\Db\Profiler;

class AdapterServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dbParams = array(
            // buffer_results - only for mysqli buffered queries, skip for others
            'options' => array('buffer_results' => true),
            'charset' => 'UTF8'
        );

        $dbal = $serviceLocator->get('doctrine.connection.orm_default');
        $mysqli = $dbal->getWrappedConnection()->getWrappedResourceHandle();
        $driver = new \Zend\Db\Adapter\Driver\Mysqli\Mysqli($mysqli);
        $adapter = new \BjyProfiler\Db\Adapter\ProfilingAdapter($driver);
        /*
          $adapter = new BjyProfiler\Db\Adapter\ProfilingAdapter(array(
          'driver'    => 'mysqli',
          //'dsn'       => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'] . '',
          'database'  => $dbParams['database'],
          'username'  => $dbParams['username'],
          'password'  => $dbParams['password'],
          'hostname'  => $dbParams['hostname'],
          'options'   => $dbParams['options'],
          'charset'	=> $dbParams['charset']
          //'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"),
          // For mysqli - 'charset' => 'UTF8'
          )); */

        // CLI at false
        if (php_sapi_name() == 'cli' && false) {
            $logger = new Logger();
            // write queries profiling info to stdout in CLI mode
            $writer = new Writer\Stream('php://stderr');
            $logger->addWriter($writer, Logger::WARN);
            $adapter->setProfiler(new Profiler\LoggingProfiler($logger));
        } else {
            $adapter->setProfiler(new Profiler\Profiler());
        }
        if (isset($dbParams['options']) && is_array($dbParams['options'])) {
            $options = $dbParams['options'];
        } else {
            $options = array();
        }
        $adapter->injectProfilingStatementPrototype($options);

        return $adapter;
    }
}

<?php

namespace OpenstoreApi\Api;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Soluble\FlexStore\Store;
use Soluble\FlexStore\Source\Zend\SqlSource;

abstract class AbstractService implements AdapterAwareInterface, ServiceLocatorAwareInterface {

    /**
     * @var ServiceLocatorInterface 
     */
    protected $serviceLocator;

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @param Adapter $adapter
     */
    function __construct(ServiceLocatorInterface $serviceLocator = null, Adapter $adapter = null) {
        if ($serviceLocator !== null) {
            $this->setServiceLocator($serviceLocator);
        }
        if ($adapter !== null) {
            $this->setDbAdapter($adapter);
        }
    }

    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return \OpenstoreApi\Api\AbstractService
     */
    public function setDbAdapter(Adapter $adapter) {

        $this->adapter = $adapter;
        return $this;
    }

    /**
     * 
     * @return Adapter
     */
    public function getDbAdapter() {
        return $this->adapter;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return \OpenstoreApi\Api\AbstractService
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
    
    /**
     * 
     * @param Select $select
     * @return Store
     */
    public function getStore(Select $select=null)
    {
        return new Store(new SqlSource($this->getDbAdapter(), $select));
    }

}

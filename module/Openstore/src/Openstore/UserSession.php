<?php

namespace Openstore;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;
use Openstore\Permission\UserCapabilities;

class UserSession implements ServiceLocatorAwareInterface {

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var Container
     */
    protected $container;

    function __construct(Container $container) {
        $this->container = $container;
    }

    function getCapabilities() {
        if ($this->container) {
            $user_id = 1;
            $user_cap = new UserCapabilities($user_id);
        }
    }

    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Openstore\Service
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * 
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

}

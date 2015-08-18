<?php

namespace Openstore;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Permission implements ServiceLocatorAwareInterface
{
    /**
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var
     */
    protected $adapter;

    public function __construct()
    {
    }

    /**
     * Return currenctly logged in identity
     * @return \Openstore\Entity\User|false
     */
    public function getIdentity()
    {
        $auth = $this->serviceLocator->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            return $auth->getIdentity();
        }
        return false;
    }

    public function setServiceLocator(ServiceLocatorInterface $sl)
    {
        $this->serviceLocator = $sl;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}

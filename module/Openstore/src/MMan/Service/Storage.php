<?php

namespace MMan\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Gaufrette\Adapter as GaufretteAdapter;
use Gaufrette\Filesystem;

class Storage implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var \Gaufrette\Adapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $adapterOptions;

    /**
     *
     * @var \Gaufrette\Filesystem
     */
    protected $filesystemInstance;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @return \Gaufrette\Filesystem
     */
    public function getFilesystem()
    {
        if ($this->filesystemInstance === null) {
            $this->filesystemInstance = new Filesystem($this->getAdapter());
        }
        return $this->filesystemInstance;
    }

    /**
     *
     * @param \Gaufrette\Adapter $adapter
     * @return \MMan\Service\Manager
     */
    public function setAdapter(GaufretteAdapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     *
     * @return \Gaufrette\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     *
     * @param array $adapterOptions
     * @return \MMan\Service\Storage
     */
    public function setAdapterOptions(array $adapterOptions)
    {
        $this->adapterOptions = $adapterOptions;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Openstore\Service
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}

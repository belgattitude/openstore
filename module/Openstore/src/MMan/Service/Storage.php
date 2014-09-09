<?php

namespace MMan\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Gaufrette\Adapter as GaufretteAdapter;
use Gaufrette\Filesystem;

class Storage implements ServiceLocatorAwareInterface {

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
    function __construct() {
        
    }

    /**
     * @return \Gaufrette\Filesystem
     */
    function getFilesystem() {
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
    function setAdapter(GaufretteAdapter $adapter) {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * 
     * @return \Gaufrette\Adapter
     */
    function getAdapter() {
        return $this->adapter;
    }

    /**
     * 
     * @param array $adapterOptions
     * @return \MMan\Service\Storage
     */
    function setAdapterOptions(array $adapterOptions) {
        $this->adapterOptions = $adapterOptions;
        return $this;
    }

    /**
     * 
     * @return array
     */
    function getAdapterOptions() {
        return $this->adapterOptions;
    }

    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Openstore\Service
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

}

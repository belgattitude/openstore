<?php

namespace OpenstoreApi\Api;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

abstract class AbstractService implements AdapterAwareInterface, 
										ServiceLocatorAwareInterface
{

	/**
	 * @var ServiceLocatorInterface 
	 */
	protected $serviceLocator;
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;
	
	
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 */
	function __construct(ServiceLocatorInterface $serviceLocator=null, Adapter $adapter=null) {
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
	 * @return \Zend\Db\Adapter\Adapter
	 */
	public function getDbAdapter() 
	{
		return $this->adapter;
	}
	
   /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     * @return \OpenstoreApi\Api\AbstractService
     */
    public function setServiceManager(ServiceManager $serviceManager) 
	{
		$this->serviceManager = $serviceManager;
		return $this;
	}		
	
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
	 * @return \OpenstoreApi\Api\AbstractService
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		
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
	
	
}
		


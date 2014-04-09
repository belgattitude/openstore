<?php

namespace Openstore\Core\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

abstract class AbstractModel implements AdapterAwareInterface, 
										ServiceLocatorAwareInterface
{

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
	function __construct(ServiceLocatorInterface $serviceLocator=null, Adapter $adapter=null) {
		if ($serviceLocator !== null) $this->setServiceLocator($serviceLocator);
		if ($adapter !== null) $this->setDbAdapter($adapter);
	}
	
	
	
	
    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return AbstractModel
     */
    public function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		return $this;
	}	
	
	
	/**
	 * 
	 * @return Adapter
	 */
	public function getDbAdapter() 
	{
		return $this->adapter;
	}
	
   /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     * @return \Openstore\Core\Model\AbstractModel
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
	 * @return \Openstore\Core\Model\AbstractModel
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
		


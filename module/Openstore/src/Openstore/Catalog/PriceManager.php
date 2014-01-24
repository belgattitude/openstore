<?php
namespace Openstore\Catalog;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterAwareInterface;




class PriceManager implements ServiceLocatorAwareInterface, AdapterAwareInterface
{
	/**
	 * @var ServiceLocatorInterface
	 */
    protected $serviceLocator;
	
	
	/**
	 *
	 * @var \Openstore\Configuration $configuration
	 */
	public $configuration;

	
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;
	
	
	/**
	 * 
	 * @param \Openstore\Configuration $options
	 */
	function __construct(Configuration $configuration, Adapter $adapter)
	{
		$this->setConfiguration($configuration);
		$this->setDbAdapter($adapter);
	}
	
	
	
	
	
	/**
	 * 
	 * @param \Openstore\Configuration $configuration
	 * @return \Openstore\Catalog\PriceManager
	 */
	public function setConfiguration(Configuration $configuration) {
		$this->configuration = $configuration;
		return $this;
	}

	/**
	 * 
	 * @return \Openstore\Configuration
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @return \Openstore\Catalog\PriceManager
	 */
	public function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		return $this;
	}
	
	
	/**
	 * 
	 * @return Zend\Db\Adapter\Adapter
	 */
	function getDbAdapter()
	{
		return $this->adapter;
	}
	
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return \Openstore\Catalog\PriceManager
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
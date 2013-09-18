<?php
namespace Openstore;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Openstore\Catalog\Filter as CatalogFilter;

class Service implements ServiceLocatorAwareInterface
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
	 * @param \Openstore\Options $options
	 */
	function __construct(Configuration $options, Adapter $adapter)
	{
		$this->configuration = $options;
		$this->setAdapter($adapter);
	}
	
	/**
	 * 
	 * @param Openstore\Catalog\BrowserAbstract
	 */
	function getCatalogBrowser($key) {
		
		//$browser = $this->getServiceLocator($key);
		
		//$browser->setAdapter($this->serviceLocator->get('Zend\Db\Adapter\Adapter'));
		return $browser;
	}
	
	
	
	function setAdapter(Adapter $adapter)
	{
		$this->adapter = $adapter;
	}
	
	function getAdapter()
	{
		return $this->adapter;
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
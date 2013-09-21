<?php

namespace Openstore\Catalog\Element;
use Openstore\Element\FilterableInterface;
use Openstore\Element\SearchableInterface;
use Openstore\Element\Searchable\Params;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

abstract class AbstractElement implements FilterableInterface, 
										  SearchableInterface, 
										  AdapterAwareInterface,
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
	 * @var Openstore\Element\Searchable\Params
	 */
	protected $searchParams;
	
	/**
	 *
	 * @var type 
	 */
	protected $filters;
	
	
	/**
	 * 
	 * @return Openstore\Element\Searchable\Params
	 */
	abstract public function getSearchableParams();
	
	
	/** 
	 *  
	 * @param array $params
	 * @return Openstore\Element\SearchableInterface
	 */
	public function setSearchParams(Params $params) {
		$activeParams = $this->getSearchableParams();
		$missing_params = array();
		
		foreach($activeParams as $name => $options) {
			if (array_key_exists($name, $params)) {
				$params[$name] = $params[$name];
			} elseif ($options['required']) {
				$missing_params[] = $name;
			}
		}
		if (count($missing_params > 0)) {
			throw new \Exception('Missing params (' . join(',', $missing_params) . ')');
		}
		$this->searchParams = $params;
		
	}

	/**
	 * @return Openstore\Element\Searchable\Params
	 */
	public function getSearchParams() {
		
		return $this->searchParams;
	}
	
	
	
	
	
	/**
	 * 
	 */
	function addFilter(Filter $filter) {
		
	}
	
	/**
	 * 
	 */
	function removeFilter($filter_name) {
	}
	
	/**
	 * 
	 */
	function getFilters() {
		
	}
	
    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return \Openstore\Catalog\Element\AbstractElement
     */
    public function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		return $this;
	}	
	
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
	 * @return \Openstore\Catalog\Element\AbstractElement
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
		

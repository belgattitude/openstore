<?php

namespace Openstore\Core\Model\Browser\Filter;

use Openstore\Core\Model\Browser\Filter\FilterInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractFilter implements FilterInterface, ServiceLocatorAwareInterface
{
	/**
	 *
	 * @var \Zend\ServiceManager\ServiceLocatorInterface
	 */
	protected $serviceLocator;
	
	/**
	 * 
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Zend\Db\Sql\Select $select
	 */
	abstract public function filter(\Zend\Db\Sql\Select $select);
	
	/**
	 * 
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return \Openstore\Core\Model\Browser\Filter\AbstractFilter
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		return $this;
	}

}


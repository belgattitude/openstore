<?php

namespace Openstore\Catalog;

use Openstore\Core\Model\Browser\Filter\FilterInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProductFilters implements ServiceLocatorAwareInterface
{
    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var array
     */
    protected $filters;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->filters = array();
        $this->setServiceLocator($serviceLocator);
    }

    /**
     *
     * @param \Openstore\Core\Model\Browser\Filter\FilterInterface $filter
     * @return \Openstore\Catalog\ProductFilters
     */
    public function register(FilterInterface $filter)
    {
        if ($filter instanceof ServiceLocatorAwareInterface) {
            if ($filter->getServiceLocator() === null) {
                $filter->setServiceLocator($this->getServiceLocator());
            }
        }
        $this->filters[$filter->getName()] = $filter;
        return $this;
    }

    public function getFilter($name)
    {
        return $this->filters[$name];
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Openstore\Core\Model\Browser\Filter\AbstractFilter
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
}

<?php

namespace Openstore\Core\Model\Browser;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\Browser\Filter\FilterInterface;
use Openstore\Core\Model\Browser\Search\Params;
use Zend\Db\Sql\Select;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Soluble\FlexStore\Store;

abstract class AbstractBrowser implements SearchableInterface, FilterableInterface, ServiceLocatorAwareInterface, AdapterAwareInterface
{
    /**
     *
     * @var AbstractModel
     */
    protected $model;

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
     * @var int
     */
    protected $limit;

    /**
     *
     * @var int
     */
    protected $offset;

    /**
     *
     * @var array
     */
    protected $columns;

    /**
     *
     * @var array
     */
    protected $filters;

    /**
     * @param AbstractModel $model
     */
    public function __construct(AbstractModel $model)
    {
        $this->model = $model;
        $this->setServiceLocator($model->getServiceLocator());
        $this->setDbAdapter($model->getDbAdapter());
    }

    /**
     * @param array|\Openstore\Core\Model\Browser\Search\Params $params
     * @return \Openstore\Core\Model\Browser\AbstractBrowser
     */
    public function setSearchParams($searchParams)
    {
        if (is_array($searchParams)) {
            $searchParams = new Params($searchParams);
        } elseif (!$searchParams instanceof Params) {
            throw new \Exception('Params must be array or Core\Model\Browser\Params');
        }

        $missing_params = [];
        $searchable = $this->getSearchableParams();
        foreach ($searchable as $name => $options) {
            if ($searchParams->offsetExists($name)) {
                // test for type or set default here
            } elseif ($options['required']) {
                $missing_params[] = $name;
            }
        }
        if (count($missing_params) > 0) {
            throw new \Exception("method setSearchParams() requires (" . implode(',', $missing_params) . ')');
        }
        $this->searchParams = $searchParams;
        return $this;
    }

    /**
     * @return \Openstore\Core\Model\Browser\Search\Params
     */
    public function getSearchParams()
    {
        return $this->searchParams;
    }

    /**
     * @return \Zend\Db\Sql\Select
     */
    abstract protected function getSelect();

    /**
     * @param \Zend\Db\Sql\Select
     * @return \Zend\Db\Sql\Select
     */
    protected function assignFilters(Select $select)
    {
        $filters = $this->getFilters();
        foreach ($filters as $filter) {
            $filter->filter($select);
        }
        return $select;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        if ($this->filters === null) {
            $this->filters = [];
        }
        return $this->filters;
    }

    /**
     * @param array $filters
     * @return \Openstore\Core\Model\Browser\AbstractBrowser
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
        return $this;
    }

    /**
     *
     * @param FilterInterface $filter
     * @return AbstractBrowser
     */
    public function addFilter(FilterInterface $filter)
    {
        if ($this->filters === null) {
            $this->filters = [];
        }
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * @param array $columns
     * @return \Openstore\Core\Model\Browser\AbstractBrowser
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     *
     * @param int $limit
     * @param int $offset
     * @return \Openstore\Core\Model\Browser\AbstractBrowser
     */
    public function setLimit($limit, $offset = null)
    {
        $this->limit = $limit;
        if ($offset !== null) {
            $this->setOffset($offset);
        }
        return $this;
    }

    /**
     *
     * @param int $offset
     * @return \Openstore\Core\Model\Browser\AbstractBrowser
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     *
     * @return Store
     */
    public function getStore()
    {
        $select = $this->getSelect();

        $store = new Store(new SqlSource($this->adapter, $select));
        if ($this->limit !== null) {
            $options = $store->getSource()->getOptions();
            $options->setLimit($this->limit, $this->offset);
        }
        return $store;
    }

    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return \Openstore\Core\Model\AbstractModel
     */
    public function setDbAdapter(Adapter $adapter)
    {
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
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}

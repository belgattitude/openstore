<?php

namespace Openstore;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Openstore\ConfigurationAwareInterface;
use Openstore\Catalog\ProductFilters;
use Openstore\UserContext;
use Openstore\Permission;

class Service implements ServiceLocatorAwareInterface, AdapterAwareInterface, ConfigurationAwareInterface
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
     * @var \Openstore\Catatog\ProductFilters
     */
    protected $productFilters;

    /**
     *
     * @param \Openstore\Configuration $options
     */
    public function __construct(Configuration $configuration, Adapter $adapter)
    {
        $this->setConfiguration($configuration);
        $this->setDbAdapter($adapter);
    }

    /**
     *
     * @return \Openstore\Catalog\Browser\ProductFilter\NewProducts
     */
    public function getProductFilters()
    {
        if ($this->productFilters === null) {
            $this->productFilters = new ProductFilters($this->serviceLocator);
            $this->productFilters->register(new \Openstore\Model\Filter\Product\AllProducts());
            $this->productFilters->register(new \Openstore\Model\Filter\Product\OnstockProducts());
            $this->productFilters->register(new \Openstore\Model\Filter\Product\NewProducts());
            $this->productFilters->register(new \Openstore\Model\Filter\Product\PromoProducts());
            $this->productFilters->register(new \Openstore\Model\Filter\Product\FavouriteProducts());
        }
        return $this->productFilters;
    }

    /**
     * @return \Openstore\Model\AbstractModel
     */
    public function getModel($name)
    {
        $model = $this->serviceLocator->get($name);
        $model->setDbAdapter($this->adapter);
        $model->setServiceLocator($this->serviceLocator);
        return $model;
    }

    /**
     *
     * @return int
     */
    public function getLoggedInUserId()
    {
        $auth = $this->serviceLocator->get('zfcuser_auth_service');
        if (!$auth->hasIdentity()) {
            throw new \Exception('Not logged in user');
        }
        $user_id = $auth->getIdentity()->getUserId();
        return $user_id;
    }

    /**
     *
     * @return \Openstore\UserContext
     */
    public function getUserContext()
    {
        $userContext = new UserContext();
        $userContext->setServiceLocator($this->getServiceLocator());
        //$userContext->initialize();
        //$userCapabilities = $this->getServiceLocator()->get('Openstore\UserCapabilities');
        //$customers = $user->getCustomers();
        //$userContext->setCustomerId($customer_id);

        return $userContext;
    }

    /**
     *
     * @param \Openstore\Configuration $configuration
     * @return \Openstore\Catalog\PriceManager
     */
    public function setConfiguration(Configuration $configuration)
    {
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
     * @return \Openstore\Service
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     *
     * @return Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
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

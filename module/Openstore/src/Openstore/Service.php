<?php
namespace Openstore;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Openstore\Catalog\ProductFilters;

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
	 * @var \Openstore\Catatog\ProductFilters
	 */
	protected $productFilters;
	
	
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
	function getModel($name) {
		$model = $this->serviceLocator->get($name);
		$model->setDbAdapter($this->adapter);
		$model->setServiceLocator($this->serviceLocator);
		return $model;
	}
	
	/**
	 * 
	 * @param Openstore\Catalog\BrowserAbstract
	 */
	function getBrowser($key) {
		
		//$filters = $this->getFilters();
		
		switch (strtolower($key)) {
			case 'brand' :
				
				break;
			case 'product' :

				$browser = new \Openstore\Catalog\Browser\Product($this->adapter);
				break;
			case 'category' :
				
				break;
			default:
				throw new \Exception("Cannot find browser with key '$key'");
		}
		
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
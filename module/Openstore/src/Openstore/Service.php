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
	
	
	public function getFilters()
	{
		$flag_new_minimum_date = date('2012-06-30');
		$filters = array(
			'all'		=>	new \Openstore\Catalog\Browser\ProductFilter\AllProducts(),
			'new'		=>	new \Openstore\Catalog\Browser\ProductFilter\NewProducts(array('minimum_date' => $flag_new_minimum_date)),
			'promos'	=>	new \Openstore\Catalog\Browser\ProductFilter\PromoProducts(),
			'onstock'	=>	new \Openstore\Catalog\Browser\ProductFilter\OnstockProducts(),
			'favourite' =>	new \Openstore\Catalog\Browser\ProductFilter\FavouriteProducts()
		);
		return $filters;
		
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
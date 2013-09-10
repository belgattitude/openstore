<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Openstore\Controller;

use Openstore\Entity;
use Openstore\Catalog\Browser\ProductFilter;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

use Openstore\Catalog\Helper\SearchParams;




class StoreController extends AbstractActionController
{
	
	/**
	 *
	 * @var \Openstore\Options
	 */
	protected $config;
	
	protected $adapter;
	
	public function __construct()
	{
		
		// Setting configuration
		ProductFilter::setParam('flag_new_minimum_date', date('2012-06-30'));
		ProductFilter::registerFilter('all',		new \Openstore\Catalog\Browser\ProductFilter\AllProducts());
		ProductFilter::registerFilter('new',		new \Openstore\Catalog\Browser\ProductFilter\NewProducts());
		ProductFilter::registerFilter('promos',		new \Openstore\Catalog\Browser\ProductFilter\PromoProducts());
		ProductFilter::registerFilter('onstock',	new \Openstore\Catalog\Browser\ProductFilter\OnstockProducts());
		ProductFilter::registerFilter('favourite',	new \Openstore\Catalog\Browser\ProductFilter\FavouriteProducts());
	}
	
	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		parent::onDispatch($e);
		$this->config = $this->getServiceLocator()->get('Openstore/Config');
		$this->adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
	}
	
	
	protected function productAction()
	{
		
	}

	
	
    public function browseAction()
    {
		$view		  = new ViewModel();
		$searchParams = SearchParams::createFromRequest($this->params());
		
		$this->layout()->searchParams = $searchParams;		
		
		// 1. get Categories
		$categoryBrowser = new \Openstore\Catalog\Browser\Category($this->adapter, $this->getFilter());
		$categoryParams = new \Openstore\Catalog\Browser\SearchParams\Category();
		$categoryParams->setIncludeEmptyNodes($include_empty_nodes=false);
		$categoryParams->setDepth($depth=1);
		$categoryParams->setFilter($searchParams->getFilter());
		$categoryParams->setBrands($searchParams->getBrands());
		
		$categoryParams->setExpandedCategory($searchParams->getFirstCategory());
		/*
		echo '<pre>';
		var_dump($categoryBrowser->getData($categoryParams)->toArray()); die();
		 */
		 
		$view->categories = $categoryBrowser->getData($categoryParams);
		
		
		// 2. get Brands
		$brandBrowser = new \Openstore\Catalog\Browser\Brand($this->adapter, $this->getFilter());
		$brandParams = new \Openstore\Catalog\Browser\SearchParams\Brand();
		$brandParams->setFilter($searchParams->getFilter());
		//$brandParams->setCategories($searchParams->getCategories());
		$view->brands = $brandBrowser->getData($brandParams);
		
		// 3. getProducts

		$productBrowser	= new \Openstore\Catalog\Browser\Product($this->adapter, $this->getFilter());
		$productParams = new \Openstore\Catalog\Browser\SearchParams\Product();
		$productParams->setQuery($searchParams->getQuery());
		$productParams->setBrands($searchParams->getBrands());
		$productParams->setFilter($searchParams->getFilter());
		//$productParams->setLimit($searchParams->getLimit());
		
		$productParams->setCategories($searchParams->getCategories());
		$store = $productBrowser->getStore($productParams);

		$store->getOptions()->setLimit($searchParams->getLimit())
							->setOffset(($searchParams->getPage() - 1) * $searchParams->getLimit());
		
		//var_dump($store->getData());
		//die();
		$view->products = $store->getData();
		
		// Setting other variables
		
		$view->searchParams = $searchParams;
		
		$catBrowser	  = new \Openstore\Catalog\Browser\Category($this->adapter, $this->getFilter());		
		$view->category_breadcrumb = $catBrowser->getAncestors($searchParams->getFirstCategory());
		
        return $view;
    }

	function getFilter()
	{
		$pricelist = $this->params()->fromRoute('pricelist');
		$language  = $this->params()->fromRoute('ui_language');
		//var_dump($language . '_' . $pricelist);
		return new \Openstore\Catalog\Filter($pricelist, $language);
	}
	
	
	public function searchAction()
	{
		$options = array(
			'query' => $this->params()->fromQuery('query')
		);
		$products = $this->getProducts($options);
		$json = new JsonModel(array(
					'products'	 => $products->toArray()
                ));	
        return $json;
		
	}
	
		
}

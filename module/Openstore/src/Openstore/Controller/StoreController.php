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
	
	public function __construct()
	{
		// Setting configuration
		
		ProductFilter::setParam('flag_new_minimum_date', date('2012-06-30'));
		ProductFilter::registerFilter('all',	new \Openstore\Catalog\Browser\ProductFilter\AllProducts());
		ProductFilter::registerFilter('new',	new \Openstore\Catalog\Browser\ProductFilter\NewProducts());
		ProductFilter::registerFilter('promos',	new \Openstore\Catalog\Browser\ProductFilter\PromoProducts());
		ProductFilter::registerFilter('onstock',new \Openstore\Catalog\Browser\ProductFilter\OnstockProducts());
		
	}
	
	public function indexAction()
	{
		$view = new ViewModel();
		return $view;
	}
	
    public function browseAction()
    {
		$config = $this->getServiceLocator()->get('Openstore/Config');
		$view = new ViewModel();
		
		$searchParams = SearchParams::createFromRequest($this->params());
		
		$this->layout()->searchParams = $searchParams;		

		$adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		
		// 1. get Categories
		$categoryBrowser = new \Openstore\Catalog\Browser\Category($adapter, $this->getFilter());
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
		$brandBrowser = new \Openstore\Catalog\Browser\Brand($adapter, $this->getFilter());
		$brandParams = new \Openstore\Catalog\Browser\SearchParams\Brand();
		$brandParams->setFilter($searchParams->getFilter());
		//$brandParams->setCategories($searchParams->getCategories());
		$view->brands = $brandBrowser->getData($brandParams);
		
		// 3. getProducts

		$productBrowser	= new \Openstore\Catalog\Browser\Product($adapter, $this->getFilter());
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
		
		
		$catBrowser	  = new \Openstore\Catalog\Browser\Category($adapter, $this->getFilter());		
		$view->category_breadcrumb = $catBrowser->getAncestors($searchParams->getFirstCategory());
		// Test with doctrine
		//$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		//$this->printCategories();
		
		//$profiler = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')->getProfiler();
		//$queryProfiles = $profiler->getQueryProfiles();		
		
        return $view;
    }

	public function productAction()
	{
		echo 'cool';
		die();
	}
	
	function getFilter()
	{
		$pricelist = $this->params()->fromRoute('pricelist');
		$language  = $this->params()->fromRoute('ui_language');
		//var_dump($language . '_' . $pricelist);
		return new \Openstore\Catalog\Filter($pricelist, $language);
	}
	
	function getBrands($search_options)
	{
        $adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		$brandBrowser	  = new \Openstore\Catalog\Browser\Brand($adapter, $this->getFilter());

		
		
		return $brandBrowser->getData($options);
		
	}
	
	
	
	function getProducts($search_options)
	{
        $adapter		= $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		$productBrowser	= new \Openstore\Catalog\Browser\Product($adapter, $this->getFilter());
		
		$options = new \Openstore\Catalog\Browser\Search\Options\Product();
		$options->setKeywords($search_options['query']);
		$options->setBrand($search_options['brand']);
		$options->setCategory($search_options['category']);
		
		$store = $productBrowser->getStore($options);

		var_dump($search_options);
		$store->getOptions()->setLimit($search_options['limit'])
							->setOffset(($search_options['page'] - 1) * $search_options['limit']);
		
		$results = $store->getData();
		//var_dump($results->getTotalRows());
		return $results;
		
		//return $productBrowser->getData($options);
		
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

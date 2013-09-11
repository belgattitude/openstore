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
use Smart\Data\Store;

use Openstore\Catalog\Helper\SearchParams;

use Zend\Stdlib\Hydrator;



class SearchController extends AbstractActionController
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
		$this->config	= $this->getServiceLocator()->get('Openstore/Config');
		$this->adapter	= $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		parent::onDispatch($e);
	}
	
	
	function indexAction() {
		die('searchcontroller');
	}
	
	
	public function productAction()
	{
		$searchParams = SearchParams::createFromRequest($this->params());
		/*
		$options = array(
			'query' => $this->params()->fromQuery('query')
		);*/
		$productBrowser	= new \Openstore\Catalog\Browser\Product($this->adapter, $this->getFilter());
		$productParams = new \Openstore\Catalog\Browser\SearchParams\Product();
		$productParams->setQuery($this->params()->fromQuery('query'));
		$store = $productBrowser->getStore($productParams);
		$store->getOptions()->setLimit($searchParams->getLimit())
							->setOffset(($searchParams->getPage() - 1) * $searchParams->getLimit());
		$writer = new \Smart\Data\Store\Writer\Zend\JsonModel($store);
		$json = $writer->getData();
        return $json;
	}

	
	public function brandAction()
	{
		$searchParams = SearchParams::createFromRequest($this->params());
		/*
		$options = array(
			'query' => $this->params()->fromQuery('query')
		);*/
		$brandBrowser	= new \Openstore\Catalog\Browser\Brand($this->adapter, $this->getFilter());
		$brandParams = new \Openstore\Catalog\Browser\SearchParams\Brand();
		$brandParams->setQuery($this->params()->fromQuery('query'));
		$store = $brandBrowser->getStore($brandParams);
		$store->getOptions()->setLimit($searchParams->getLimit())
							->setOffset(($searchParams->getPage() - 1) * $searchParams->getLimit());

		
		$writer = new \Smart\Data\Store\Writer\Zend\JsonModel($store);
		$json = $writer->getData();
		
        return $json;
		
	}	

	/**
	 * 
	 * @return \Openstore\Catalog\Filter
	 */
	protected function getFilter()
	{
		$pricelist = $this->params()->fromRoute('pricelist');
		$language  = $this->params()->fromRoute('ui_language');
		//var_dump($language . '_' . $pricelist);
		return new \Openstore\Catalog\Filter($pricelist, $language);
	}
		
}
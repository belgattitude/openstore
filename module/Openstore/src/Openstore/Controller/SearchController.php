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

use Smart\Data\Store\Adapter\ZendDbSqlSelect;

use Openstore\Model\Product;


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
		//$this->config	= $this->getServiceLocator()->get('Openstore\Config');
		$this->adapter	= $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		parent::onDispatch($e);
	}
	
	
	function indexAction() {
		die('searchcontroller');
	}
	
	public function testAction() {


		$searchParams = SearchParams::createFromRequest($this->params());
		
		
		/*
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$product = new Product($sl, $adapter);
		*/
		$product = $this->getServiceLocator()->get('Openstore\Service')->getModel('Model\Product');
		$browser = $product->getBrowser()->setSearchParams(
							[
								'query' => 'SW201',
								'pricelist' => 'FR',
								'language' => 'en'
							])
							->setLimit(10, $offset=0)
							->setColumns(
								array(
									'product_id'		=> new Expression('p.product_id'),
									'reference'			=> new Expression('p.reference'),
									'brand_title'		=> new Expression('pb.title'),
									'category_reference'=> new Expression('pc.reference'),
									'category_title'	=> new Expression('COALESCE(pc18.title, pc.title)'),
									'title'				=> new Expression('COALESCE(p18.title, p.title)'),
									'invoice_title'		=> new Expression('COALESCE(p18.invoice_title, p.invoice_title)'),
								)						
							);
							//->addFilter('promos');
							
		
		
		$store = $browser->getStore();
		
		$writer = new \Smart\Data\Store\Writer\Json($store);
		$json = $writer->send();
		
		return $json;
		
		var_dump($store->getData());
		die();
		
		die('cool');
		
		/*
		$product = new Searchable\Product();
		$configuration = new Searchable\Configuration();
		$configuration->setAdapter();
		$configuration->setServiceLocator();
		$product->setConfiguration();	
		
		$store = $product->search()->setSearchParams()
								   ->addFilter()
								   ->addPlugin()
						           ->setLimit()
						           ->setColumns()
						           ->getStore();
						  
		*/
	}
	
	
	public function productAction()
	{
		$searchParams = SearchParams::createFromRequest($this->params());
		
		/*
		$sl = $this->getServiceLocator();
		$adapter = $sl->get('Zend\Db\Adapter\Adapter');
		
		$product = new Product($sl, $adapter);
		*/
		$product = $this->getServiceLocator()->get('Openstore\Service')->getModel('Model\Product');
		$browser = $product->getBrowser()->setSearchParams(
							[
								'query' => $searchParams->getQuery(),
								'pricelist' => $searchParams->getPricelist(),
								'language' => $searchParams->getLanguage()
							])
							->setLimit(20, $offset=0)
							->setColumns(
								array(
									'product_id'		=> new Expression('p.product_id'),
									'reference'			=> new Expression('p.reference'),
									'brand_title'		=> new Expression('pb.title'),
									'category_reference'=> new Expression('pc.reference'),
									'category_title'	=> new Expression('COALESCE(pc18.title, pc.title)'),
									'title'				=> new Expression('COALESCE(p18.title, p.title)'),
									'invoice_title'		=> new Expression('COALESCE(p18.invoice_title, p.invoice_title)'),
								)						
							);
							//->addFilter('promos');
							
		
		
		$store = $browser->getStore();
		
		$writer = new \Smart\Data\Store\Writer\Json($store);
		$json = $writer->send();
	}

	
	public function brandAction()
	{
		$pricelist = $this->params()->fromRoute('pricelist');
		$language  = $this->params()->fromRoute('ui_language');
		
		$searchParams = SearchParams::createFromRequest($this->params());
		/*
		$options = array(
			'query' => $this->params()->fromQuery('query')
		);*/
		$brandBrowser	= new \Openstore\Catalog\Browser\Brand($this->adapter);
		$brandParams = new \Openstore\Catalog\Browser\SearchParams\Brand();
		$brandParams->setQuery($this->params()->fromQuery('query'));
		$brandParams->setLanguage($language);
		$brandParams->setPricelist($pricelist);
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
		//var_dump($language . '_' . $pricelist);
		return new \Openstore\Catalog\Filter($pricelist, $language);
	}
		
}
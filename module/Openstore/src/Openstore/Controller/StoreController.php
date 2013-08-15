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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;


class BrowseParams
{
	/**
	 * @var ArrayObject
	 */
	protected $params;
	
	
	function __construct() {
		$this->params = new \ArrayObject();
	}
	
	/**
	 * 
	 * @param \Openstore\Controller\Zend\Mvc\Controller\Plugin\Params $params
	 * @return \Openstore\Controller\BrowseParams
	 */
	static function createFromRequest(Zend\Mvc\Controller\Plugin\Params $params) {
		$browseParams = new BrowseParams();
		
		
		$browseParams->setBrands();
		$browseParams->setBrowseFilter($params->fromRoute('browse_filter', 'all'));
		$browseParams->setCategories($params->fromRoute('category_reference'));
		$browseParams->setKeywords($params->fromRoute('query', ''));
		$browseParams->setLimit($params->fromRoute('perPage', 20));
		$browseParams->setPage($params->fromRoute('page', 1));
		$browseParams->setSortBy($params->fromRoute('sort_by'));
		$browseParams->setSortDirection($params->fromRoute('sort_dir', 'ASC'));
		
		return $browseParams; 
	}
	
	/**
	 * 
	 * @param type $keywords
	 * @return \Openstore\Controller\BrowseParams
	 */
	function setKeywords($keywords) {
		$this->params['keywords'] = $keywords;
		return $this;
	}
	function setCategories($categories) {
		$this->params['categories'] = $keywords;
		return $this;
		
	}
	
	function setBrands() {
		
		$this->params['brands'] = $keywords;
		return $this;
		
	}
	
	function setBrowseFilter() {
		
		$this->params['browse_filter'] = $keywords;
		return $this;
		
	}
	
	function setPage() {
		
		$this->params['page'] = $keywords;
		return $this;
		
	}
	
	function setPerPage() {
		
		$this->params['keywords'] = $keywords;
		return $this;
		
	}
	function setSortBy()
	{
		$this->params['sortby'] = $keywords;
		return $this;
		
	}
	
	function setSortDirection()
	{
		
		$this->params['sort_direction'] = $keywords;
		return $this;
		
	}
}

class StoreController extends AbstractActionController
{
	
	public function indexAction()
	{
		$view = new ViewModel();
		return $view;
	}
	
    public function browseAction()
    {
		
		$config = $this->getServiceLocator()->get('Openstore/Config');
		$view = new ViewModel();
		$options = array(
			'browse_filter' => $this->params()->fromRoute('browse_filter', 'all'),
			'query'		=> $this->params()->fromQuery('query'),
			'category'	=> $this->params()->fromRoute('category_reference'),
			'brand'		=> $this->params()->fromRoute('brand_reference'),
			'page'		=> (int) $this->params()->fromRoute('page'),
			'limit'		=> (int) $this->params()->fromRoute('perPage', 20),
			
		);
		

		//var_dump($this->params()->fromQuery());
		//var_dump($this->params()->fromRoute());
		//var_dump($options);
		
		$brands		= $this->getBrands($options);
		$categories = $this->getCategories($options);
		
		
		$products	= $this->getProducts($options);
		
		$view->brands		= $brands;
		
		$view->categories	= $categories;
		$view->products		= $products;
		$view->searchOptions= $options;
		
		$adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		$catBrowser	  = new \Openstore\Catalog\Browser\Category($adapter, $this->getFilter());		
		$view->category_breadcrumb = $catBrowser->getAncestors($options['category']);
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
	
	function getCategories($search_options)
	{
        $adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		 //$di = new \Zend\Di\Di();
		 //$di->newInstance('Openstore\Catalog\Browser\Category', array('filter' => $this->getFilter())	);
		 //die();
		$catBrowser	  = new \Openstore\Catalog\Browser\Category($adapter, $this->getFilter());

		//$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		//$catBrowser->test($em);
		
		
		$options = new \Openstore\Catalog\Browser\Search\Options\Category();		
		$options->setIncludeEmptyNodes($include_empty_nodes=false);
		$options->setDepth($depth=1);
		$options->setExpandedCategory($search_options['category']);
		$options->setBrand($search_options['brand']);
		//$options = new \Openstore\Catalog\Browser\Search\Options();
		return $catBrowser->getData($options);
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

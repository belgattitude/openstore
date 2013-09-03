<?php

namespace Openstore\Catalog\Helper;

class SearchParams
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
	 * @return \Openstore\Controller\searchParams
	 */
	static function createFromRequest(\Zend\Mvc\Controller\Plugin\Params $params) {
		$searchParams = new SearchParams();
		$searchParams->setFilter($params->fromRoute('filter', 'all'));
		//var_dump($params->fromRoute('categories')); die();
		$categories = $params->fromRoute('categories');
		if (trim($categories) == '') {
			$searchParams->setCategories(null);
		} else {
			$searchParams->setCategories(explode(',', $categories));
		}
		

		$brands = $params->fromRoute('brands');
		if (trim($brands) == '') {
			$searchParams->setBrands(null);
		} else {
			$searchParams->setBrands(explode(',', $brands));
		}
		
		
		$searchParams->setQuery($params->fromRoute('query', ''));
		$searchParams->setLimit($params->fromRoute('perPage', 20));
		$searchParams->setPage($params->fromRoute('page', 1));
		$searchParams->setSortBy($params->fromRoute('sortBy'));
		$searchParams->setSortDir($params->fromRoute('sortDir', 'ASC'));
		$searchParams->setLanguage($params->fromRoute('ui_language', 'en'));
		$searchParams->setPricelist($params->fromRoute('pricelist'));
		
		return $searchParams; 
	} 
	
	/**
	 * 
	 * @return \ArrayObject
	 */
	function toArray()
	{
		return $this->params;
	}
	
	function setLanguage($language)
	{
		$this->params['language'] = $language;
		return $this;
	}
	
	function getLanguage()
	{
		return $this->params['language'];
	}
	
	function setPricelist($pricelist)
	{
		$this->params['pricelist'] = $pricelist;
		return $this;
	}
	
	function getPricelist()
	{
		return $this->params['pricelist'];
	}
	
	
	/**
	 * 
	 * @param type $keywords
	 * @return \Openstore\Controller\searchParams
	 */
	function setQuery($query) {
		$this->params['query'] = $query;
		return $this;
	}
	
	function getQuery()
	{
		$this->params['query'];
	}
	
	function setCategories($categories) {
		
		$categories = (array) $categories;
		
		if (count($categories) == 0) {
			$this->params['categories'] = null; 
		} else {
			
			$this->params['categories'] = $categories;
			
		}
		return $this;
		
	}
	
	function getCategories()
	{
		return $this->params['categories'];
	}
	
	function getFirstCategory()
	{
		if (is_array($this->params['categories']) && count($this->params['categories']) > 0) {
			
			return $this->params['categories'][0];
		}
		
		return null;
	}
	
	function setBrands($brands) {

		$brands = (array) $brands;
		if (count($brands) == 0) {
			$this->params['brands'] = null; 
		} else {
			$this->params['brands'] = $brands;
		}
		
		return $this;
		
	}
	
	function getBrands() {
		return $this->params['brands'];
	}

	function getFirstBrand()
	{
		if (is_array($this->params['brands']) && count($this->params['brands']) > 0) {
			return $this->params['brands'][0];
		}
		return null;
	}
	
	function setFilter($filter) {
		
		$this->params['filter'] = $filter;
		return $this;
		
	}
	
	function getFilter()
	{
		return $this->params['filter'];
	}
	
	function setPage($page) {
		
		$this->params['page'] = $page;
		return $this;
	}
	
	function getPage() {
		return $this->params['page'];
	}
	
	function setLimit($limit) {
		
		$this->params['limit'] = $limit;
		return $this;
		
	}
	
	function getLimit() {
		return $this->params['limit'];
	}
	
	function setSortBy($sortBy)
	{
		$this->params['sortBy'] = $sortBy;
		return $this;
		
	}
	
	function getSortBy() {
		return $this->params['sortBy'];
	}
	
	function setSortDir($sortDir)
	{
		$this->params['sortDir'] = $sortDir;
		return $this;
	}
	
	function getSortDir()
	{
		return $this->params['sortDir'];
	}
}
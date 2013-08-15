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
		$searchParams->setCategories($params->fromRoute('categories'));
		$searchParams->setBrands($params->fromRoute('brands'));
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
		$this->params['categories'] = $categories;
		return $this;
		
	}
	
	function getCategories()
	{
		return $this->params['categories'];
	}
	
	function setBrands($brands) {
		
		$this->params['brands'] = $brands;
		return $this;
		
	}
	
	function getBrands() {
		return $this->params['brands'];
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
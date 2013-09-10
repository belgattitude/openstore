<?php
namespace Openstore\Catalog\Browser\SearchParams;

class Product extends SearchParamsAbstract
{
	/**
	 * @var array 
	 */
	protected $brands;

	/**
	 * @var array 
	 */
	protected $categories;	
	
	/**
	 * @var string
	 */
	protected $query;
	
	function getParams()
	{
		return array(
			'brands' => $this->getBrands(),
			'categories' => $this->getCategories(),
			'query'		=> $this->getQuery(),
			
		);
	}

	function setLimit($limit)
	{
		$this->limit = $limit;
	}
	
	function getLimit()
	{
		return $this->limit;
	}
	
	
		
	
	function setBrands($brands)
	{
		$this->brands = (array) $brands;
	}
	
	function getBrands()
	{
		return $this->brands;
	}	
	
	/**
	 * 
	 * @param string $category
	 * @return \Openstore\Catalog\Browser\Search\Options\Product
	 */
	function setCategories($categories)
	{
		$this->categories = (array) $categories;
		return $this;
	}
	
	
	function getCategories()
	{
		return $this->categories;
	}
	
	
	function setQuery($query)
	{
		$this->query = $query;
	}
	
	function getQuery()
	{
		return $this->query;
	}
	
}

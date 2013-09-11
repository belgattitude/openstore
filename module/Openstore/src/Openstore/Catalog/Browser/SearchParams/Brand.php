<?php

namespace Openstore\Catalog\Browser\SearchParams;

class Brand extends SearchParamsAbstract
{
	/**
	 *
	 * @var array
	 */
	protected $categories;

	
	/**
	 *
	 * @var string 
	 */
	protected $query;
	
	
	function getParams()
	{
		return array(
			'categories' => $this->getCategories(),
		);
	}
	
	function setQuery($query)
	{
		$this->query = $query;
	}
	
	function getQuery()
	{
		return $this->query;
	}
	
	/**
	 * 
	 * @param array|string $categories
	 * @return \Openstore\Catalog\Browser\SearchParams\Brand
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
	
	
}

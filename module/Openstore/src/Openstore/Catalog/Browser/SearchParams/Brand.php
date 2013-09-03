<?php

namespace Openstore\Catalog\Browser\SearchParams;

class Brand extends SearchParamsAbstract
{
	/**
	 *
	 * @var array
	 */
	protected $categories;
	
	
	
	function getParams()
	{
		return array(
			'categories' => $this->getCategories(),
		);
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

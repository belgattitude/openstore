<?php
namespace Openstore\Catalog\Browser\Search\Options;
use Openstore\Catalog\Browser\Search\Options; 

class Brand extends Options
{
	protected $keywords;
	
	function __construct()
	{
		
	}
	
	/**
	 * 
	 * @param string $category
	 * @return \Openstore\Catalog\Browser\Search\Options\Brand
	 */
	function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}
	
	
	function getCategory()
	{
		return $this->category;
	}
	
	
}

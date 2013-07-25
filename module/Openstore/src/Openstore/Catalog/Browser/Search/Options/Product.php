<?php
namespace Openstore\Catalog\Browser\Search\Options;
use Openstore\Catalog\Browser\Search\Options; 

class Product extends Options
{
	protected $keywords;
	
	function __construct()
	{
		
	}
	
	function setBrand($brand)
	{
		$this->brand = $brand;
	}
	
	function getBrand()
	{
		return $this->brand;
	}	
	
	/**
	 * 
	 * @param string $category
	 * @return \Openstore\Catalog\Browser\Search\Options\Product
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
	
	
	function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}
	
	function getKeywords()
	{
		return $this->keywords;
	}
	
	
}

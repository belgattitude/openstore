<?php
namespace Openstore\Catalog\Browser\Search\Options;
use Openstore\Catalog\Browser\Search\Options; 
class Category extends Options
{
	/**
	 *
	 * @var boolean
	 */
	protected $include_empty_nodes;
	
	protected $depth;
	
	function __construct()
	{
		$this->depth = 0;
	}
	
	/**
	 * 
	 * @param string $expanded_category
	 * @return \Openstore\Catalog\Browser\Search\Options\Category
	 */
	function setExpandedCategory($expanded_category)
	{
		$this->expanded_category = $expanded_category;
		return $this;
	}
	
	
	function getExpandedCategory()
	{
		return $this->expanded_category;
	}
	
	/**
	 * 
	 * @param int $depth
	 * @return \Openstore\Catalog\Browser\Search\Options\Category
	 */
	function setDepth($depth)
	{
		$this->depth = $depth;
		return $this;
	}
	
	/**
	 * 
	 * @return int
	 */
	function getDepth()
	{
		return $this->depth;
	}
	
	/**
	 * 
	 * @param boolean $include_empty_nodes
	 * 
	 */
	function setIncludeEmptyNodes($include_empty_nodes)
	{
		$this->include_empty_nodes = $include_empty_nodes;
		return $this;
	}
	
	function getIncludeEmptyNodes()
	{
		return $this->include_empty_nodes;
	}
	
	function setBrand($brand)
	{
		$this->brand = $brand;
	}
	
	function getBrand()
	{
		return $this->brand;
	}
	
	
}

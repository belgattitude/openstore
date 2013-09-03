<?php
namespace Openstore\Catalog\Browser\SearchParams;

class Category extends SearchParamsAbstract
{
	/**
	 * @var array
	 */
	protected $brands;
	
	
	/**
	 *
	 * @var boolean
	 */
	protected $include_empty_nodes;
	
	/**
	 *
	 * @var int
	 */
	protected $depth;
	
	function __construct()
	{
		$this->depth = 0;
	}
	
	function getParams()
	{
		return array(
			'brands' => $this->getBrands(),
			'expanded_category' => $this->getExpandedCategory(),
			'depth' => $this->getDepth(),
			'include_empty_nodes' => $this->getIncludeEmptyNodes()
		);
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
	
	function setBrands($brands)
	{
		$this->brands = (array) $brands;
	}
	
	function getBrands()
	{
		return $this->brands;
	}
	
	
}

<?php
namespace Openstore\Catalog\Browser;

use \Openstore\Catalog\Browser\ProductFilter\FilterAbstract;

class ProductFilter
{
	/**
	 *
	 * @var array 
	 */
	static protected $filters;
	
	static protected $params;
	
	private function __construct() {
		
	}
	
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return ProductFilter
	 */
	static function setParam($key, $value) {
		self::$params[$key] = $value;
	}
	
	/**
	 * 
	 * @param string $key
	 * @return mixed
	 */
	static function getParam($key) {
		return self::$params[$key];
	}
	
	/**
	 * 
	 * @param string $key
	 * @param \Openstore\Catalog\Browser\ProductFilter\FilterAbstract $filter
	 * @return \Openstore\Catalog\Browser\ProductFilter
	 */
	static function registerFilter($key, FilterAbstract $filter)
	{
		self::$filters[$key] = $filter;
		return $this;
	}
	
	/**
	 * 
	 * @return array
	 */
	static function getFilters()
	{
		return self::$filters;
	}
	
	/**
	 * @param string $key
	 * @return \Openstore\Catalog\Browser\ProductFilter\FilterAbstract
	 */
	static function getFilter($key)
	{
		$filter = self::$filters[$key];
		return $filter;
	}
	
	
}
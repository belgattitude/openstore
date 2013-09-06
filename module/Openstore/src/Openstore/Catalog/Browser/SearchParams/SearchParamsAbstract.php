<?php

namespace Openstore\Catalog\Browser\SearchParams;

abstract class SearchParamsAbstract
{
	/**
	 * @return array
	 */
	abstract function getParams();
	
	
	function setFilter($filter)
	{
		$this->filter = $filter;
	}
	
	function getFilter()
	{
		return $this->filter;
	}	 
}
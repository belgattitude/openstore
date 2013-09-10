<?php

namespace Openstore\Catalog\Browser\SearchParams;

abstract class SearchParamsAbstract
{
	/**
	 * @return array
	 */
	abstract function getParams();
	
	
	function setId($id)
	{
		$this->id = $id;
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function setFilter($filter)
	{
		$this->filter = $filter;
	}
	
	function getFilter()
	{
		return $this->filter;
	}	 
}
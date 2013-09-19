<?php

namespace Openstore\Catalog\Browser\SearchParams;

abstract class SearchParamsAbstract
{
	protected $language;
	protected $pricelist;
	protected $id;
	
	
	
	
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
	
	function setLanguage($language)
	{
		$this->language = $language;
	}
	
	function setPricelist($pricelist)
	{
		$this->pricelist = $pricelist;
	}

	function getLanguage()
	{
		return $this->language;
	}
	
	function getPricelist()
	{
		return $this->pricelist;
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
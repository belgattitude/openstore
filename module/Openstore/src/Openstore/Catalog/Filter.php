<?php

namespace Openstore\Catalog;

class Filter
{
	protected $pricelist;
	protected $language;
	
	function __construct($pricelist, $language) {
		$this->setLanguage($language);
		$this->setPricelist($pricelist);
	}
	
	function setLanguage($language)
	{
		$this->language = $language;
	}
	
	function getLanguage()
	{
		return $this->language;
	}
	
	function setPricelist($pricelist)
	{
		$this->pricelist = $pricelist;
	}
	
	function getPricelist()
	{
		return $this->pricelist;
	}
	
}
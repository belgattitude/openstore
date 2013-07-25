<?php

namespace Openstore\Catalog\Browser\Search;

class Options
{
	protected $keywords;
	
	function __construct()
	{
		
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
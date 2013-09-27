<?php

namespace MMan\Import;

class Element {
	
	protected $filename;
	
	function __construct()
	{
		
		
	}
	
	
	function setFilename($filename) 
	{
		$this->filename = $filename;
	}
	
	function getFilename()
	{
		return $this->filename;
	}
	
}
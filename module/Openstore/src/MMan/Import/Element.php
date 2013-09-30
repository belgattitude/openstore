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
		return $this;
	}
	
	function getFilename()
	{
		return $this->filename;
	}
	
	function getFilesize()
	{
		return filesize($this->filename);
	}
	
	function getFilemtime()
	{
		return filemtime($this->filename);
	}
	
	function getLegacyMapping() 
	{
		return $this->legacy_mapping;
	}
	
	function setLegacyMapping($legacy_mapping) 
	{
		$this->legacy_mapping = $legacy_mapping;
		return $this;
	}
	
}
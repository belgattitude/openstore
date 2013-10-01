<?php

namespace MMan;
use ArrayObject;

class Media {
	
	
	/**
	 * @var \ArrayObject
	 */
	protected $properties;
	
	
	function __construct() {
		
	}
	
	
	
	/**
	 * 
	 * @param array|ArrayObject $properties
	 * @return \MMan\Media
	 */
	function setProperties($properties) {
		if (is_array($properties)) {
			$properties = new ArrayObject($properties);
		}
		if (!$properties instanceof ArrayObject) {
			throw new \Exception("Invalid usage, setPorperties needs an array or ArrayObject as argument");
		}
		$this->properties = $properties;
		return $this;
	}
	
	/**
	 * @return string 
	 */
	function getPath() 
	{
		$file = str_replace('//', '/', $this->properties['folder'] . '/' . $this->properties['location']);
		if (!file_exists($file)) throw new \Exception("File '$file' does not exists");
		if (!is_readable($file)) throw new \Exception("File '$file' is not readable");
		if (!is_file($file)) throw new \Exception("File '$file' is not a file");
		$path = realpath($file);
		return $path;
		
		
		
	}
	
}
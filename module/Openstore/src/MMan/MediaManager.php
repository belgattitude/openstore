<?php

namespace MMan;

use MMan\Service\Storage;
use MMan\Media;
use Zend\Db\Adapter\Adapter;	
	
class MediaManager {
	
	/**
	 * @var \MMan\Service\Storage
	 */
	protected $storage;
	
	/**
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;
	
	function __construct() {
		
	}
	
	
	function import(Media $media) {
		$fs = $this->storage->getFilesystem();
		if ($media->getMediaId() !== '') {
			
			
		}
	}
	
	
	
	/**
	 * 
	 * @param \MMan\Service\Storage $storage
	 * @return \MMan\MediaManager
	 */
	function setStorage(Storage $storage) {
		$this->storage = $storage;
		return $this;
	}
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @return \MMan\MediaManager
	 */
	function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		return $this;
	}
	
	
}
<?php

namespace MMan;

class Media {
	
	
	
	function __construct() {
		
	}
	
	function setFilename($filename) {
		
		$this->filename = $filename;
		return $this;
		
	}
	
	function getFilename() {
		return $this->filename;
	}
	
	function setFilesize($filesize) {
		$this->filesize = $filesize;
		return $this;
	}
	
	function getFilesize() {
		$this->filesize;
	}
	
	function setMediaId($media_id) {
		$this->media_id = $media_id;
		return $this;
	}
	
	function getMediaId() {
		return $this->mediaId;
	}
	
}
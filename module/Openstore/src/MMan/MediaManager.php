<?php

namespace MMan;

use MMan\Service\Storage;
use MMan\Media;
use MMan\Import\Element as ImportElement;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Stdlib;

use Smart\Model\Table;

class MediaManager
{

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
	
	

	/**
	 * 
	 * @param \MMan\Import\Element $element
	 * @param int $container_id
	 * @param boolean $overwrite
	 */
	function import(ImportElement $element, $container_id, $overwrite=true) {

		
		$fs = $this->storage->getFilesystem();

		// STEP 1 : Adding into database
		
		$table = new Table($this->adapter);
		
		$media = $table->findOneBy('media', 'legacy_mapping', $element->getLegacyMapping());
		
		$unchanged = false;
		if ($media !== false) {
			// test if media has changed
			if ($media['filemtime'] == $element->getFilemtime() &&
				$media['filesize'] == $element->getFilesize()) {
				$unchanged = true;
			}
		}
		
		if (!$unchanged) {
			$this->adapter->getDriver()->getConnection()->beginTransaction();					
			
			$filename = $element->getFilename();
			$data  = array(
				'filename'  => basename($filename),
				'filemtime' => $element->getFilemtime(),
				'filesize'  => $element->getFilesize(),
				'container_id' => $container_id,
				'legacy_mapping' => $element->getLegacyMapping()
			);
			
			
			$media = $table->insertOnDuplicateKey('media', $data, $duplicate_exclude=array('legacy_mapping'));
			$media_id = $media['media_id'];
			
			// Step 3 : Generate media manager filename
			
			$mediaLocation = $this->getMediaLocation($container_id, $media_id, $filename);
			
			// Step 2 : Adding into filesystem
			try {
				$fs->write($mediaLocation['filename'], file_get_contents($filename), $overwrite);
				
			} catch (\Exception $e) {
				// If something goes wrong throw an exception
				$this->adapter->getDriver()->getConnection()->rollback();		
				throw $e;
			}

			// @todo make a super try catch ;)
			/*
			 * Relative location of file
			 */
			$media['location'] = $mediaLocation['location'];
			$media->save();
			
			$this->adapter->getDriver()->getConnection()->commit();		
			
			
		}
		
		return $media['media_id'];
	}
	

	/**
	 * @param int $container_id
	 * @param int $media_id
	 * @param string $filename
	 * @return array
	 */
	function getMediaLocation($container_id, $media_id, $filename) {

		$table = new Table($this->adapter);
		$container = $table->find('media_container', $container_id);
		if ($container ===  false) {
			throw new \Exception("Cannot locate container '$container_id'");
		}
		$container_folder = $container['folder'];
		
		if ($media_id == '') {
			throw new \Exception("Cannot create media location, media_id '$media_id' is required");
		}
		$pathinfo = pathinfo($filename);
		
		if ($pathinfo['extension'] == '') {
			$ext = '';
		} else {
			$ext = '.' . $pathinfo['extension'];
		}
		
		// Should better be handled by iconv with translate but need a dependency
		$qf = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $pathinfo['filename']);
		
		$media_directory = $this->getMediaDirectory($media_id);
		$media_location = $media_directory . '/' . "$media_id-" . substr($qf, 0, 40) . $ext;
		$media_filename = $container_folder . '/' . $media_location;
		
		$location = array(
			'filename' => $media_filename,
			'location' => $media_location
		);
		
		return $location;
	}
	
	/**
	 * 
	 * @param int $media_id
	 * @return string
	 */
	protected function getMediaDirectory($media_id) {
		
		$dirs = array();
		$dirs[] = str_pad(substr($media_id, 0, 2), 2, 0, STR_PAD_LEFT);
		$dirs[] = str_pad(substr($media_id, 2, 4), 2, 0, STR_PAD_LEFT);
		$dir = join('/', $dirs);
		return $dir;
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
<?php

namespace MMan;

use MMan\Service\Storage;
use MMan\Media;
use MMan\Import\Element as ImportElement;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Stdlib;
use Soluble\Normalist\SyntheticTable;



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
	
	/**
	 *
	 * @var SyntheticTable
	 */
	protected $syntheticTable;

	function __construct() {
		
	}
	
	
	/**
	 * 
	 * @param integer $media_id
	 * @return \MMan\Media
	 */
	function get($media_id) {
		$syntheticTable = $this->getSyntheticTable();
		try {
			$media_record = $syntheticTable->find('media', $media_id);
		} catch (\Smart\Model\Exception\RecordNotFoundException $e) {
			throw new \Exception("Cannot locate media '$media_id'");
		}
		$container_record = $media_record->getParent('media_container');
		$media = new Media($this);
		$media->setProperties(array(
			'filename'		=> $media_record->filename,
			'filesize'		=> $media_record->filesize,
			'mimetype'		=> $media_record->mimetype,
			'location'		=> $media_record->location,
			'title'			=> $media_record->title,
			'description'	=> $media_record->description,
			'created_at'	=> $media_record->created_at,
			'updated_at'	=> $media_record->updated_at,
			'container_id'	=> $media_record->container_id,
			'folder'		=> $container_record->folder
		));
		
		
		return $media;
		
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
		
		$syntheticTable = $this->getSyntheticTable();
		
		
		$media = $syntheticTable->findOneBy('media', array('legacy_mapping' => $element->getLegacyMapping()));
		
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
			
			
			$media = $syntheticTable->insertOnDuplicateKey('media', $data, $duplicate_exclude=array('legacy_mapping'));
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

		$syntheticTable = new Table($this->adapter);
		
		$container = $syntheticTable->find('media_container', $container_id);
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
	 * \MMan\Service\Storage $storage
	 * @return 
	 */
	function getStorage()
	{
		return $this->storage;
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
	

	/**
	 * 
	 * @return SyntheticTable
	 */
	function getSyntheticTable() {
		if ($this->syntheticTable === null) {
			$this->syntheticTable = new SyntheticTable($this->adapter);
		}
		return $this->syntheticTable;
	}
	
	
	/**
	 * 
	 * @param \Soluble\Normalist\SyntheticTable $syntheticTable
	 * @return \MMan\MediaManager
	 */
	function setSyntheticTable(SyntheticTable $syntheticTable) {
		$this->syntheticTable = $syntheticTable;
		return $this;
	}

}
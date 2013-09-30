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
	 * @param type $media_id
	 * @param type $filename
	 * @return string
	 */
	function getMediaFilename($container_id, $media_id, $filename) {

		$table = new Table($this->adapter);
		$container = $table->find('media_container', $container_id);
		if ($container ===  false) {
			throw new \Exception("Cannot locate container '$container_id'");
		}
		$container_folder = $container['folder'];
		
		
		if ($media_id == '') {
			throw new \Exception("Media id '$media_id' is required");
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
		$media_filename = $container_folder . '/' . $media_directory . '/' . "$media_id-" . substr($qf, 0, 40) . $ext;
		return $media_filename;
	}
	
	function getMediaDirectory($media_id) {
		
		$dirs = array();
		$dirs[] = str_pad(substr($media_id, 0, 2), 2, 0, STR_PAD_LEFT);
		$dirs[] = str_pad(substr($media_id, 2, 4), 2, 0, STR_PAD_LEFT);
		$dir = join('/', $dirs);
		return $dir;
	}
	

	/**
	 * 
	 * @param \MMan\Import\Element $element
	 * @param int $container_id
	 * @param type $folder
	 */
	function import(ImportElement $element, $container_id) {

		
		$fs = $this->storage->getFilesystem();

		// STEP 1 : Adding into database
		$this->adapter->getDriver()->getConnection()->beginTransaction();		
		
		$table = new Table($this->adapter);
		
		$media = $table->findOneBy('media', 'legacy_mapping', $element->getLegacyMapping());
		
		$unchanged = false;
		if ($media !== false) {
			// test if media has changed
			if ($media['filemtime'] != $element->getFilemtime() ||
				$media['filesize'] != $element->getFilesize()) {
				$unchanged = true;
			}
		}

		$filename = $element->getFilename();
		$data  = array(
			'filename'  => basename($filename),
			'filemtime' => $element->getFilemtime(),
			'filesize'  => $element->getFilesize(),
			'container_id' => $container_id,
			'legacy_mapping' => $element->getLegacyMapping()
		);
		
		
		if (!$unchanged) {
			$media = $table->insertOnDuplicateKey('media', $data, $duplicate_exclude=array('legacy_mapping'));
			$media_id = $media['media_id'];

			
			
			
			// Step 3 : Generate media manager filename

			$media_filename = $this->getMediaFilename($container_id, $media_id, $filename);

			// Step 2 : Adding into filesystem
			try {
				$fs->write($media_filename, file_get_contents($filename));
			} catch (\Exception $e) {
				// If something goes wrong throw an exception
				$this->adapter->getDriver()->getConnection()->rollback();		
				throw $e;
			}

			// @todo make a super try catch ;)
			$media['location'] = $media_filename;
			$media->save();
			
			$this->adapter->getDriver()->getConnection()->commit();		
			return $media_id;
			
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
<?php

namespace MMan;

use MMan\Service\Storage;
use MMan\Media;
use MMan\Import\Element as ImportElement;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

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
	 * @param type $container
	 * @param type $folder
	 */
	function import(ImportElement $element, $container = null, $folder = null) {

		$fs = $this->storage->getFilesystem();

		// STEP 1 : Adding into database
		$this->adapter->getDriver()->getConnection()->beginTransaction();		
		
		$sql = new Sql($this->adapter);
		$insert = $sql->insert('media');
		$filename = $element->getFilename();
		$data  = array(
			'filename'  => basename($filename),
			'filemtime' => filemtime($filename),
			'filesize'  => filesize($filename),
			'legacy_mapping' => $element->getLegacyMapping()
		);
		$insert->values($data);

		$statement = $sql->prepareStatementForSqlObject($insert);
		$result    = $statement->execute();
		$media_id  = $this->adapter->getDriver()->getLastGeneratedValue();
		
		// Step 2 : Adding into filesystem
		try {
			$fs->write($media_id, file_get_contents($filename));
		} catch (\Exception $e) {
			// If something goes wrong throw an exception
			$this->adapter->getDriver()->getConnection()->rollback();		
			throw $e;
		}
		$this->adapter->getDriver()->getConnection()->commit();		
		return $media_id;
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
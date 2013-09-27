<?php

namespace Akilia\Utils;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterAwareInterface;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

use Smart\Data\Store\Adapter\ZendDbSqlSelect;

class Akilia1Products implements ServiceLocatorAwareInterface, AdapterAwareInterface {
	
	/**
	 * @var ServiceLocatorInterface
	 */
    protected $serviceLocator;
	
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;
	
	/**
	 *
	 * @var array
	 */
	protected $configuration;
	
	
	function __construct(array $configuration) {
		$this->configuration = $configuration;
	}
	
	
	function getActiveProducts() {
		$akilia1db  = $this->configuration['synchronizer']['db_akilia1'];
		
		$select = new Select();
		$select->from(array("a" => new \Zend\Db\Sql\TableIdentifier('article', $akilia1db)), array())
			   ->where('a.flag_archive <> 1');

		$select->columns(array(
				'id_article'	=> new Expression('TRIM(a.id_article)'),
				'reference'		=> new Expression('a.reference'),
				'id_marque'		=> new Expression('a.id_marque')
			), true);
		
		
		$store = new ZendDbSqlSelect(['select'  => $select,
									  'adapter' => $this->getDbAdapter()]);
		
		$data = $store->getData();
		return $data;		
	}
	
	function getProductPictures() {
		
		$image_path = $this->configuration['product_picture_path'];
		
		if (!is_dir($image_path)) {
			throw new \Exception("Image path '$image_path' is not correct update your config");
		}
		
		$products = $this->getActiveProducts()->toArray();
		$pcache = array_column($products, 'reference', 'id_article');
		
		
		$images = array();
		foreach (glob("$image_path/*.jpg") as $filename) {
			$basename = basename($filename);
				
				$product_id = trim(preg_replace('/(\_\d){0,1}\.jpg$/', '', $basename));
				
				preg_match('/(\_)(\d){0,1}\.jpg$/', $basename, $matches);				
				if (count($matches) === 3) {
					
					$index = $matches[2];
				} else {
					$index = null;
				}
				$product_active = array_key_exists($product_id, $pcache);
				$images[] = array(
					'product_id' => $product_id, 
					'filename' => $filename, 
					'basename' => $basename,
					'product_active' => $product_active, 
					'alternate_index' => $index,
					'filemtime' => filemtime($filename),
					'filesize' => filesize($filename),
					'md5' => md5($filename) 
				);
		}
		return $images;
	}
	
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 */
	public function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		return $this;
	}
	
	
	/**
	 * 
	 * @return Zend\Db\Adapter\Adapter
	 */
	function getDbAdapter()
	{
		return $this->adapter;
	}
	
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

	/**
	 * 
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
		
}
<?php
namespace Openstore\Catalog\Browser;

use Openstore\Catalog\Browser\Search\Options as SearchOptions;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;

use Zend\Db\Sql\Sql;

use Smart\Data\Store;
use Smart\Data\Store\Adapter\ZendDbSqlSelect;




abstract class BrowserAbstract implements AdapterAwareInterface 
{
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;

	
	/**
	 *
	 * @var \Openstore\Catalog\Filter
	 */
	protected $filter;
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 * @param \Openstore\Catalog\Filter $filter
	 */
	function __construct(Adapter $adapter, \Openstore\Catalog\Filter $filter)
	{
		$this->setDbAdapter($adapter);
		$this->filter = $filter;
	}
	
	
	function setDbAdapter(Adapter $adapter)
	{
		
		$this->adapter = $adapter;
	}
	

	/**
	 * @return SqlInterface
	 */
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\Search\Options $options
	 * @return \Zend\Db\Sql\Select
	 */
	abstract function getSelect(SearchOptions $options=null);
	
	/**
	 * 
	 * @return \Openstore\Catalog\Browser\Search\Options $options
	 */
	function getDefaultOptions()
	{
		$options = new \Openstore\Catalog\Browser\Search\Options();		
		return $otions;
	}
	
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\Search\Options $options
	 * @return \Smart\Data\Store\Adapter\Adapter
	 */
	function getStore(SearchOptions $options=null)
	{
		if ($options === null) {
			$options = $this->getDefaultOptions();
		}
		$select = $this->getSelect($options);
		$store = new ZendDbSqlSelect(['select'  => $select,
									  'adapter' => $this->adapter]);
		return $store;
	}
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\Search\Options $options
	 * @return array
	 */
	function getData(SearchOptions $options=null)
	{
		
		$sql = new Sql($this->adapter);
		if ($options === null) {
			$options = $this->getDefaultOptions();
		}
		$select = $this->getSelect($options);
		
		
		$store = new ZendDbSqlSelect(['select'  => $select,
									  'adapter' => $this->adapter]);
		
				
		$results = $store->getData();
		
		return $results;
		
		$sql_string = $sql->getSqlStringForSqlObject($select);
		
		
		
		
		//echo '<pre>';
		//var_dump($sql_string);die();
		
		$results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);			
		return $results;
	}

		
}
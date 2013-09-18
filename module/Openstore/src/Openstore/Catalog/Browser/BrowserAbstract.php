<?php
namespace Openstore\Catalog\Browser;

use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;

use Zend\Db\Sql\Sql;

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
	function __construct(Adapter $adapter)
	{
		$this->setDbAdapter($adapter);
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
	 * @param \Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract $params
	 * @return \Zend\Db\Sql\Select
	 */
	abstract function getSelect(SearchParams $params=null);
	
	/**
	 * 
	 * @return \Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract $params
	 */
	abstract function getDefaultParams();
	
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract $params
	 * @return \Smart\Data\Store\Adapter\Adapter
	 */
	function getStore(SearchParams $params=null)
	{
		if ($params === null) {
			$params = $this->getDefaultParams();
		}
		$select = $this->getSelect($params);
		$store = new ZendDbSqlSelect(['select'  => $select,
									  'adapter' => $this->adapter]);
		return $store;
	}
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract $params
	 * @return array
	 */
	function getData(SearchParams $params=null)
	{
		
		$sql = new Sql($this->adapter);
		if ($params === null) {
			$params = $this->getDefaultParams();
		}
		$select = $this->getSelect($params);
		
		
		$store = new ZendDbSqlSelect(['select'  => $select,
									  'adapter' => $this->adapter]);
				
		$results = $store->getData();
		return $results;
		
	}
		
}
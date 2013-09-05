<?php
/**
 * 
 * @author Vanvelthem Sébastien
 */

namespace Smart\Data\Store\Adapter;

use Smart\Data\Store\Adapter\Adapter as StoreAdapter;
use Smart\Data\Store\ResultSet\ResultSet;
use Smart\Data\Store\Exception;
use Smart\Data\Store\Options;
use Zend\Db\Adapter\Adapter;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;



use ArrayObject;


class ZendDbSqlSelect extends StoreAdapter {
//class ZendDbSqlSelect {
	
	/**
	 *
	 * @var \Zend\Db\Sql\Select
	 */
	protected $select;
	
	
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter 
	 */
	protected $adapter;
	
	/**
	 * Initial params received in the constructor
	 * @var ArrayObject
	 */
	protected $params;
	
	/**
	 * 
	 * @param array|ArrayObject $params
	 * @throws Exception\InvalidArgumentException
	 * @throws Exception\MissingArgumentException
	 */
	function __construct($params)
	{
		if (is_array($params)) {
			$params = new ArrayObject($params);
		} elseif (!$params instanceof ArrayObject) {
			throw new Exception\InvalidArgumentException("Params must be either an ArrayObject or an array"); 
		}
		
		if ($params->offsetExists('select')) {
			if ($params['select'] instanceof \Zend\Db\Sql\Select) {
				$this->select = $params['select'];
			} else {
				throw new Exception\InvalidArgumentException("Param 'select' must be an instance of Zend\Db\Sql\Select"); 
			}
		} else {
			throw new Exception\MissingArgumentException("Missing param 'select'"); 
		}
		
		if ($params->offsetExists('adapter')) {
			if ($params['adapter'] instanceof \Zend\Db\Adapter\Adapter) {
				$this->adapter = $params['adapter'];
			} else {
				throw new Exception\InvalidArgumentException("Param 'adapter' must be an instance of Zend\Db\Adapter\Adapter"); 
			}
		} else {
			throw new Exception\MissingArgumentException("Missing param 'adapter'"); 
		}
		$this->params = $params;
	}
	
	/**
	 * 
	 * @param \Zend\Db\Sql\Select $select
	 * @param \Smart\Data\Store\Options $options
	 * @return \Zend\Db\Sql\Select
	 */
	protected function assignOptions(\Zend\Db\Sql\Select $select, \Smart\Data\Store\Options $options)
	{
		if ($options->hasLimit()) {
			$select->limit($options->getLimit());
			if (is_numeric($options->getOffset())) {
				$select->offset($options->getOffset());
			}
			
			$select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));
		}
		return $select;
	}
	
	/**
	 * 
	 * @param type $options
	 * @return Zend\Db\ResultSet\ResultSet
	 */
	function getData(Smart\Data\Store\Options $options = null)
	{
		if ($options === null) {
			$options = $this->getOptions();
		}
		$select = $this->assignOptions(clone $this->select, $options);
		
		$sql = new Sql($this->adapter);
		$sql_string = $sql->getSqlStringForSqlObject($select);
		//var_dump($sql_string);
		try {			
			
			$results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);	
			$r = new ResultSet();
			$r->initialize($results);
			$r->setStore($this);
			
			if ($options->hasLimit()) {
				$row = $this->adapter->query('select FOUND_ROWS() as total_count')->execute()->current();
				$r->setTotalRows($row['total_count']);
			} else {
				$r->setTotalRows($r->count());
			}
			
		} catch (\Exception $e) {
			echo "Error " . $e->getMessage();
			var_dump($sql_string);
			die();
		}
		return $r;
	}
	
}
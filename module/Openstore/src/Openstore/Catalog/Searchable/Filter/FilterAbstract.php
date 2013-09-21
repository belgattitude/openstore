<?php
namespace Openstore\Catalog\Browser\ProductFilter;

use Zend\Db\Sql\Select;

abstract class FilterAbstract {
	
	/**
	 *
	 * @var array
	 */
	protected $params;
	
	function __construct(array $params=array()) {
		$this->params = $params;
	}
	

	/**
	 * 
	 * @param \Zend\Db\Sql\Select $select
	 * @return FilterAbstract
	 */
	abstract function setConstraints(Select $select);
	
	
	/**  
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\FilterAbstract
	 */
	abstract function addDefaultSortClause(Select $select);
	
	
}

	

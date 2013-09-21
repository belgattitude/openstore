<?php
namespace Openstore\Core\Model\Browser\Filter;

use Zend\Db\Sql\Select;

interface FilterInterface {
	
	/**
	 * @return string
	 */
	function getName();
	
	/**
	 * 
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Zend\Db\Sql\Select
	 */
	function filter(Select $select);
}
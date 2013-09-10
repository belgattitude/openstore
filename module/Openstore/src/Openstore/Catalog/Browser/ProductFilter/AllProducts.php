<?php

namespace Openstore\Catalog\Browser\ProductFilter;

use Openstore\Catalog\Browser\ProductFilter\FilterAbstract;
use Zend\Db\Sql\Select;

class AllProducts extends FilterAbstract
{
	
	/**
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\FilterAbstract
	 */
	function setConstraints(Select $select) {
		// Do nothing
		return $this;
	}
	
	
	/**  
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\AllProducts
	 */
	function addDefaultSortClause(Select $select)
	{
		$select->order(array('p.reference' => $select::ORDER_ASCENDING));
		return $this;
	}
}
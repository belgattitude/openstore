<?php
namespace Openstore\Catalog\Element\Filter;

use Openstore\Element\Filterable\FilterInterface;
use Zend\Db\Sql\Select;

class AllProducts implements FilterInterface
{
	/**
	 * 
	 * @return string
	 */
	function getName() {
		return 'filter_all_products';
	}
	
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
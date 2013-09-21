<?php

namespace Openstore\Catalog\Browser\ProductFilter;

use Openstore\Catalog\Browser\ProductFilter\FilterAbstract;
use Zend\Db\Sql\Select;

class PromoProducts extends FilterAbstract
{
	
	/**
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\FilterAbstract
	 */
	function setConstraints(Select $select) {
		
		$select->where("(ppl.promo_discount > 0)");
		return $this;
	}
	
	
	/**  
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\AllProducts
	 */
	function addDefaultSortClause(Select $select)
	{
		$select->order(array(
			'ppl.promo_discount'=> $select::ORDER_DESCENDING, 
			'p.reference'		=> $select::ORDER_ASCENDING)
		);
		return $this;
	}
}